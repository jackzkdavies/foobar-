myApp.config(function($stateProvider, $urlRouterProvider) {

    $urlRouterProvider.otherwise('/');
    $stateProvider
      .state('home', {
        url: '/',
        views: {
          '': { templateUrl: './app/shared/main/main.html'},
          'sideBar@home': { template: './templates/assets/nav.html' }
       }
    });
  

});