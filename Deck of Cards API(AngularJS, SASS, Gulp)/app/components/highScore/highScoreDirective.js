myApp.directive("highScore", function() {
		return {
			restrict: "E",
			templateURL: '../../../app/components/highScore/highScoreDirective.html',
			scope: {
			  highScores: '='
			},
			link: function(scope, element, attrs) {

			}
		};
	});