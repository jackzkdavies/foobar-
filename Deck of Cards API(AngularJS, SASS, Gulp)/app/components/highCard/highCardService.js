myApp.factory('HighCardService', function ($http, $q) {
	 var self = {
	 	money: 0,
	 	bet: 10,
		deck: {
			// deck_id:'u9zoyq44icf4'
		},
		lastDraw : [],
		cards:52,
		winner:'',
		playerName:'abc',
		highScores:[
			{
				name:'Steve',
				score:1040
			},{
				name:'Bob',
				score:312
			},{
				name:'Jess',
				score:1040
			},{
				name:'Cat',
				score:1314
			},{
				name:'Steve',
				score:4242
			},{
				name:'Steve',
				score:2222
			},{
				name:'Steve',
				score:2344
			},
		],
		winners:[]
	};
	return {
		getDeck: function() {
			if(self.deck.deck_id){
				return $http.get('https://deckofcardsapi.com/api/deck/'+self.deck.deck_id+'/shuffle/')
					.then(function(response) {
						console.log(response);
							if (response.data.success) {
								self.deck = response.data;
								self.cards = response.data.remaining;
								return response.data;
							} else {
								self.deck = response;
								return false;
							}

					}, function(response) {
							self.deck = response;
							return false; 
					});
			}else {
				return $http.get('https://deckofcardsapi.com/api/deck/new/shuffle/?deck_count=1')
					.then(function(response) {
						console.log(response);
							if (response.data.success) {
								self.deck = response.data;
								self.cards = response.data.remaining;
								return response.data;
							} else {
								self.deck = response;
								return false;
							}

					}, function(response) {
							self.deck = response;
							return false; 
					});
			}
		},
		drawCard: function(number) {
				return $http.get('https://deckofcardsapi.com/api/deck/'+self.deck.deck_id+'/draw/?count=2')
						.then(function(response) {
							console.log(response);
								if (response.data.success) {
									self.lastDraw = response.data.cards;
									self.cards = response.data.remaining;
									return response.data.cards;
								} else {
									self.lastDraw = response;
									return false;
								}

						}, function(response) {
								self.lastDraw = response;
								return false; 
						});
		},
		getGameState: function(){
			return self;
		},
		setGameState: function(state){
			return self = state;
		},
		addHighScore: function(){
			var score = {name:self.playerName, score:self.money}
			return self.highScores.push(score);
		},
		addWinner:function(winner){
			self.winners.push(winner);
		},
		resetWinners:function(){
			self.winners = [];
		}

		
	}
});

