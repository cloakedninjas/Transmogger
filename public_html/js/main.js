var BaseView = Backbone.View.extend({
  render: function() {
    this.$el.html(this.template());
    return this;
  }
});

var App = BaseView.extend({
  className: 'app',
  
  _modelViewer = null,
  _optionsPanel = null,
  
  initialize: function () {
  
  }
});

var ModelView = BaseView.extend({
  className: 'model-viewer',
  template: '<flash></flash>'
});

var OptionsPanelView = BaseView.extend({
  className: 'options-panel',
});

var LoadoutModel = Backbone.Model.extend({
  urlRoot: '/api/loadout'
});

var ItemModel = Backbone.Model.extend({
  urlRoot: '/api/item',
  
});

var ItemCollection = Backbone.Collection.extend({
  model: ItemModel,
  url: '/api/item'
  
  
  /*
  initialize: function (models, options) {
    this._query = options.query;
  },
  
  url: function () {
    return '/api/search/item?q=' + encodeURIComponent(this._query);
  }*/
  
  search: function (query) {
    this.fetch({
      data: {
        query: query
      }
    });
  }
});