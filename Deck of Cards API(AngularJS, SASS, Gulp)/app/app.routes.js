myApp.config(function($stateProvider, $urlRouterProvider) {

    $urlRouterProvider.otherwise('/');
    $stateProvider
      .state('home', {
        url: '/',
        views: {
          '': { templateUrl: './app/shared/main/main.html'},
          'header@home': { templateUrl: './app/shared/header/header.html'},
          'highCard@home': { templateUrl: './app/components/highCard/highCard.html', controller:'HighCardController' },
          'footer@home': { templateUrl: './app/shared/footer/footer.html' }
       }
    });
  

});