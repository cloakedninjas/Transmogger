var ItemLookup = {
  maxLookups: 50,
  lookupCount: 0,
  delay: 1000,
  failTimeout: 5000,
  timer: null,

  itemId: null, // id of item being looked up
  locale: null, // locale of item being looked up

  rootUrl: 'http://eu.battle.net/api/wow/item/#itemId#?locale=#locale#&cacheBust=' + (new Date().getTime()) + '&jsonp=ItemLookup.lookupComplete',

  requestWork: function () {
    $.ajax({
      url: '/fetch/get-new-job/',
      type: 'GET',
      dataType : 'JSON',

      success: ItemLookup.checkToRunLookup
    });
  },

  fetchData: function (itemId, locale) {
    console.log('go fetch ', itemId, this);
    if (this.lookupCount < this.maxLookups) {
      var url = this.rootUrl.replace('#itemId#', itemId).replace('#locale#', locale);

      this.itemId = itemId;
      this.locale = locale;

      $('head').append('<script type="text/javascript" src="' + url + '"></script>');

      this.timer = setTimeout(this.reportLookupFailed, this.failTimeout);
    }
  },

  reportLookupFailed: function () {
    $.ajax({
      url: '/fetch/post-response/',
      type: 'POST',
      dataType : 'JSON',
      data: {
        id: ItemLookup.itemId,
        lang: ItemLookup.locale,
        failed: true
      },

      success: ItemLookup.checkToRunLookup,

      error: function () {
        ItemLookup.requestWork();
      }
    });
  },

  lookupComplete: function (response) {
    clearTimeout(this.timer);
    this.lookupCount++;

    this.reportSuccessfulLookup(response);
  },

  reportSuccessfulLookup: function (response) {
    $.ajax({
      url: '/fetch/post-response/',
      type: 'POST',
      dataType : 'JSON',
      data: {
        id: response.id,
        lang: this.locale,
        name: response.name,
        quality: response.qualitym,
        level: response.itemLevel,
        icon: response.icon,
        inv: response.inventoryType,
        itemClass: response.itemClass,
        subType: response.itemSubClass
      },

      success: function (response) {
        setTimeout(function () {
          ItemLookup.checkToRunLookup(response);
        }, ItemLookup.delay);
      },

      error: function () {
        ItemLookup.requestWork();
      }
    });
  },

  checkToRunLookup: function (response) {
    if (response) {
      ItemLookup.fetchData(response.id, response.locale);
    }
  }
};
