/*
 * Authenticator Server
 * Author : Martin Chudoba
 * Version : 0.3.0
*/
var express			= require('express');
var session			= require('express-session');
var bodyParser 		= require('body-parser');
var cookieParser 	= require('cookie-parser');
var app				= express();
var util 			= require('util');
var mysql 			= require("mysql");
var formidable      = require('formidable');

var expire = 3600000;

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

app.use(function(req, res, next) {
    res.header('Access-Control-Allow-Origin', 'http://localhost');
    res.header('Access-Control-Allow-Credentials', 'true');
    res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
    res.header('Access-Control-Allow-Headers', 'Cache-Control, X-Requested-With, Accept, Origin, Referer, User-Agent, Content-Type, Authorization');

    // intercept OPTIONS method
    if ('OPTIONS' == req.method) {
        res.sendStatus(200);
        req.session.destroy();
    }
    else {
        next();
    }
});

// Create the connection.
// Data is default to new mysql installation and should be changed according to your configuration.
var db = mysql.createConnection({
	user: "****",
	password: "****",
	database: "****"
});

var WSserver = require('./node-include/ws-server.js');
var WSserverInstance = new WSserver();

WSserverInstance.init(null, HelpersInstance);

/**
 * Login
 */
app.post('/nodejs/NodeJsAuthBridge/login',function(req,res){
	WSserverInstance.setSessionStore(req.sessionStore);
	var sess = req.session;

	var query = "SELECT * FROM `user` WHERE login LIKE '" + req.body.username + "' AND password LIKE '" + req.body.password + "';";

	db.query(query, function (error, rows, fields) {
		if (rows !== undefined && rows.length == 1) {
			sess.email = req.body.username;
			sess.login = req.body.hash;
			sess.cookie.expires = new Date(Date.now() + expire); //3600000
			sess.cookie.maxAge = expire;
			req.session.save();
			req.session.touch();
			res.end('done');
		} else {
			res.end('error');
		}
	});
});

app.post('/nodejs/NodeJsAuthBridge/upload', function(req,res) {
    var sess = req.session;
    console.log(sess);

    if(sess.email) {
        req.session.cookie.expires = new Date(Date.now() + expire); //3600000
        req.session.cookie.maxAge = expire;
        req.session.save();
        req.session.touch();

        var form = new formidable.IncomingForm(),
            files = [],
            fields = [];

        form.uploadDir = 'files/';
        form
            .on('field', function(field, value) {
                console.log(field, value);
                fields.push([field, value]);
            })
            .on('file', function(field, file) {
                console.log(field, file);
                files.push([field, file]);
            })
            .on('end', function() {
                console.log('-> upload done');
                res.writeHead(200, {'content-type': 'text/plain'});
                res.write('received fields:\n\n '+util.inspect(fields));
                res.write('\n\n');
                res.end('received files:\n\n '+util.inspect(files));
            });

        form.parse(req);
    } else {
        res.end('not logged');
    }
});

/**
 * Is logged test
 */
app.post('/nodejs/NodeJsAuthBridge/test',function(req,res){
	var sess = req.session;
	WSserverInstance.setSessionStore(req.sessionStore);
	if(sess.email) {
		/*console.log(req.sessionID);
		console.log(new Date(Date.now() + 30000));*/
		sess.email = sess.email;
		req.session.cookie.expires = new Date(Date.now() + expire); //3600000
		req.session.cookie.maxAge = expire;
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
app.get('/nodejs/NodeJsAuthBridge/logout',function(req, res) {
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
app.listen(3000, function(){
	console.log("App Started on PORT 3000");
});
