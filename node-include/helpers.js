/**
 * Created by viper on 6.2.2015.
 */

var Helpers = function() {


	/**
	 *
	 * @param login
	 * @returns {boolean}
	 */
	this.findUser = function(login, sessionStore) {
		if (sessionStore) {
			for (var x in sessionStore.sessions) {
				var date = new Date(JSON.parse(sessionStore.sessions[x]).cookie.expires);
				var today = new Date(Date.now());
				if (date - today > 0) {
					/*console.log(JSON.parse(sessionStore.sessions[x]).login);
					console.log(login);*/
					if (JSON.parse(sessionStore.sessions[x]).login == login) {
						return true;
					}
				} else {
					delete sessionStore.sessions[x];
				}
			}
		}
		return false;
	};

	/**
	 *
	 * @param login
	 * @param sessionStore
	 */
	this.removeUser = function(login, sessionStore) {
		if (sessionStore) {
			for (var x in sessionStore.sessions) {
				if (JSON.parse(sessionStore.sessions[x]).email == login) {
					delete sessionStore.sessions[x];
				}
			}
		}
	};

};

module.exports = Helpers;