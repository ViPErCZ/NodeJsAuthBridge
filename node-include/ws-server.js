/**
 * Created by viper on 6.2.2015.
 */

var WSserver = function() {

	this.wss = null;
	this.sessionStore = null;

	/**
	 *
	 * @returns {null|*}
	 */
	this.getWss = function() {
		return this.wss;
	};

	/**
	 *
	 * @param sessionStore
	 */
	this.setSessionStore = function(sessionStore) {
		this.sessionStore = sessionStore;
	};

	/**
	 *
	 */
	this.init = function(sessionStore, HelpersInstance) {

		/**
		 * WebSocket server
		 */
		var WebSocketServer = require('ws').Server;
		this.wss = new WebSocketServer({
				port: 3001
		});

		var that = this;
		that.sessionStore = sessionStore;

		/**
		 *
		 * @param data
		 */
		that.wss.broadcast = function (data) {
			var ws = this;
			for (var i in ws.clients) {
				if (that.sessionStore) {
					var users = "";
					for (var x in that.sessionStore.sessions) {
						/*console.log(sessionStore.sessions[x]);
						console.log(ws.clients[i].login);*/
						var date = new Date(JSON.parse(that.sessionStore.sessions[x]).cookie.expires);
						var today = new Date(Date.now());
						if (date - today > 0 && HelpersInstance.findUser(ws.clients[i].login, that.sessionStore)) {
							if (users != "") {
								users += ", ";
							}
							users += JSON.parse(that.sessionStore.sessions[x]).email;
						} else if (date - today < 0) {
							delete that.sessionStore.sessions[x];
						}
					}
					if (users != "") {
						var today = new Date(Date.now());
						users = today.toLocaleTimeString() + ': ' + users;
						ws.clients[i].send(users);
					}
				}
			}
		};

		/**
		 *
 		 */
		that.wss.on('connection', function (ws) {
			ws.on('message', function (message) {
				/*console.log('received: %s', JSON.parse(message));
				console.log(JSON.parse(message).login);*/
				ws.login = JSON.parse(message).login;
				if (HelpersInstance.findUser(ws.login, that.sessionStore)) {
					that.wss.broadcast(JSON.parse(message));
				}
			});
		});

	};

};

module.exports = WSserver;