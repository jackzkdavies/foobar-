myApp.controller('HighCardController', function($scope, HighCardService) {

	$scope.gameState = HighCardService.getGameState();
		$scope.$watch('gameState', function (newValue, oldValue) {
			if (newValue !== oldValue){ 
				HighCardService.setGameState(newValue)
				if (!$scope.$$phase) {$scope.$apply();};
			}
		},true);
		$scope.$watch(function () { return HighCardService.getGameState(); }, function (newValue, oldValue) {
			if (newValue !== oldValue){
			 $scope.gameState = newValue;
			}
		},true);

	$scope.startNewGame = function(){
		HighCardService.getDeck().then(function(response) {
			$scope.gameState.money = 100;
		});
	};

	$scope.takeTurn = function(){
		HighCardService.drawCard(2).then(function(cards) {
			if(cards){
				if(cards[0].value > cards[1].value){
					$scope.gameState.winner = 'player';
					$scope.gameState.money += parseInt($scope.gameState.bet);
				} else {
					$scope.gameState.winner = 'dealer';
					$scope.gameState.money += -$scope.gameState.bet;

				}
			} else {
				//error handling
			}
		});

	}
});