/*
 * Authenticator Server
 * Author : Martin Chudoba
 * Version : 0.2.0
*/
var express			= require('express');
var session			= require('express-session');
var bodyParser 		= require('body-parser');
var cookieParser 	= require('cookie-parser');
var app				= express();
var util 			= require('util');
var mysql 			= require("mysql");

var Helpers = require('./node-include/helpers.js');
var HelpersInstance = new Helpers();

app.set('views', __dirname + '/views');
app.engine('html', require('ejs').renderFile);

app.use(session({
	secret  : 'sdfsdSDFD5sf4rt4egrt4drgsdFSD4e5',
	resave: false,
	saveUninitialized: true,
	cookie  : { secure: false, maxAge : 60000, httpOnly: false }
}));


app.use(bodyParser.json());      
app.use(bodyParser.urlencoded({extended: true}));
app.use(cookieParser());

app.use(function(req, res, next) {
	next();
});

// Create the connection.
// Data is default to new mysql installation and should be changed according to your configuration.
var db = mysql.createConnection({
	user: "****",
	password: "*****",
	database: "*****"
});

var WSserver = require('./node-include/ws-server.js');
var WSserverInstance = new WSserver();

WSserverInstance.init(null, HelpersInstance);

/**
 * Login
 */
app.post('/login',function(req,res){
	WSserverInstance.setSessionStore(req.sessionStore);
	var sess = req.session;

	var query = "SELECT * FROM `user` WHERE login LIKE '" + req.body.username + "' AND password LIKE '" + req.body.password + "';";

	db.query(query, function (error, rows, fields) {
		if (rows !== undefined && rows.length == 1) {
			sess.email = req.body.username;
			sess.login = req.body.hash;
			sess.cookie.expires = new Date(Date.now() + 3600000); //3600000
			sess.cookie.maxAge = 3600000;
			req.session.save();
			req.session.touch();
			res.end('done');
		} else {
			res.end('error');
		}
	});
});

/**
 * Is logged test
 */
app.post('/test',function(req,res){
	var sess = req.session;
	WSserverInstance.setSessionStore(req.sessionStore);
	if(sess.email) {
		/*console.log(req.sessionID);
		console.log(new Date(Date.now() + 30000));*/
		sess.email = sess.email;
		req.session.cookie.expires = new Date(Date.now() + 3600000); //3600000
		req.session.cookie.maxAge = 3600000;
		req.session.save();
		req.session.touch();
		res.end(sess.email + " " + req.session.cookie.expires);
	} else {
		//console.log(req.sessionID);
		res.end('not logged');
	}
});

/**
 * Logout
 */
app.get('/logout',function(req, res) {
	HelpersInstance.removeUser(req.session.email, req.sessionStore);
	req.session.destroy(function(err){
		if(err){
			console.log(err);
		} else {
			WSserverInstance.getWss().broadcast();
		}
	});
	res.end('not logged');
});

/**
 *
 */
app.listen(3000,function(){
	console.log("App Started on PORT 3000");
});
