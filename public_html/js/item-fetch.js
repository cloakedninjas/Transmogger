var ItemLookup = {
  maxLookups: 50,
  lookupCount: 0,
  delay: 1000,
  failTimeout: 20000,
  timer: null,

  itemId: null, // id of item being looked up
  locale: null, // locale of item being looked up

  rootUrl: 'http://eu.battle.net/api/wow/item/#itemId#?locale=#locale#&jsonp=ItemLookup.lookupComplete',

  fetchData: function (itemId, locale) {
    if (this.lookupCount < this.maxLookups) {
      var url = this.rootUrl.replace('#itemId#', itemId).replace('#locale#', locale);

      this.itemId = itemId;
      this.locale = locale;

      $('head').append('<script type="text/javascript" src="' + url + '"></script>');

      this.timer = setTimeout(ItemLookup.reportLookupFailed, this.failTimeout);
    }
  },

  reportLookupFailed: function () {
    $.ajax({
      url: '/ajax/lookup-locale/',
      type: 'POST',
      dataType : 'JSON',
      data: {
        id: this.itemId,
        lang: this.locale,
        failed: true
      },

      success: this.performNextLookup
    });
  },

  lookupComplete: function (response) {
    clearTimeout(this.timer);
    this.lookupCount++;

    this.reportSuccessfulLookup(response);
  },

  reportSuccessfulLookup: function (response) {
    $.ajax({
      url: '/ajax/lookup-locale/',
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

      success: this.performNextLookup
    });
  },

  performNextLookup: function (response) {
    if (typeof response.next === 'undefined') {
      return;
    }

    if (this.lookupCount < this.maxLookups) {
      setTimeout(function () {
        ItemLookup.fetchData(response.next.id, response.next.locale);
      }, this.delay);
    }
  }
};
