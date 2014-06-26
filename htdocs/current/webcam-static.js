/*
 * Javascript that drives the still image display of webcams.
 * 
 * Feel free to use this for whatever! 
 */
var imagestore;

Ext.onReady(function(){

/* Hack needed for Ext 3.0-rc2 to keep timefield working */
Ext.override(Ext.form.ComboBox, {
    beforeBlur: Ext.emptyFn
});

Ext.override(Date, {
    toUTC : function() {
                        // Convert the date to the UTC date
        return Ext.Date.add(this, Ext.Date.MINUTE, this.getTimezoneOffset());
    },

    fromUTC : function() {
                        // Convert the date from the UTC date
        return Ext.Date.add(this, Ext.Date.MINUTE, -this.getTimezoneOffset());
    }
});



var dataFields = [
 'cid',
 'name',
 'county',
 'state',
 'network',
 'url'
];

var disableStore = new Ext.data.Store({
    idProperty  : 'cid',
    fields      : dataFields
});

imagestore = new Ext.data.JsonStore({
    isLoaded    : false,
    proxy: {
        type: 'ajax',
        url: cfg.jsonSource,
        reader: {
            type: 'json',
            idProperty : 'cid',
            rootProperty: 'images'
        }
    },
    fields      : dataFields
});
imagestore.on('load', function(store, records){
  data = Array();
  Ext.each(records, function(record){
    data.push({
      boxLabel : (record.get("cid").substr(5,3) * 1)+" "+record.get("name"), 
      name     : record.get("cid"), 
      checked  : true,
      listeners  : {
        check: function(cb, checked, oldValue){
          id = cb.getName();
          if (! imagestore.isLoaded ){ return; }

          rec = imagestore.getById( id );
          if (checked && !rec){
            rec = disableStore.getById( id );
            imagestore.add( rec );
            imagestore.sort(Ext.getCmp("sortSelect").getValue(), "ASC");
            disableStore.remove( rec );
          } else {
            rec = imagestore.getById( id );
            imagestore.remove( rec );
            disableStore.add( rec );
          }
        }
      }
    });
  });
  if (Ext.getCmp("camselector")){
      Ext.getCmp("camselector").destroy();
  }
  if (records.length > 0){
     Ext.getCmp("cameralist").add({
        xtype      : 'checkboxgroup',
        columns    : 1,
        id         : 'camselector',
        hideLabel  : true,
        items      : data
     });
     Ext.getCmp("cameralist").doLayout();
  } else {
     Ext.Msg.alert('Status', 'Sorry, no images found for this time. Try selecting a time divisible by 5.');
  }
  imagestore.isLoaded = true;
});


var tpl = new Ext.XTemplate(
    '<tpl for=".">',
        '<div class="thumb-wrap" id="{cid}">',
        '<div class="thumb"><img class="webimage" src="{url}?{[ (new Date()).getTime() ]}" title="{name}"></div>',
        '<span>[{cid}] {name}, {state} ({county} County)</span></div>',
    '</tpl>',
    '<div class="x-clear"></div>'
);

var helpWin = new Ext.Window({
    contentEl  : 'help',
    title      : 'Information',
    closeAction: 'hide',
    width      : 400
});

Ext.create('Ext.Panel', {
  renderTo : 'main',
  height: 600,
  layout   : {
	  type: 'border',
	  align: 'stretch'
  },
  items     : [{
      xtype       : 'form',
      id          : 'cameralist',
      region : 'west',
      collapsible : true,
      autoScroll  : true,
      title       : "Select Webcams",
      tbar        : [{
          xtype   : 'button',
          text    : 'Turn All Off',
          handler : function(){
              Ext.getCmp("camselector").items.each(function(i){
                   i.setValue(false);
              });
          }
      },{
          xtype   : 'button',
          text    : 'Turn All On',
          handler : function(){
              Ext.getCmp("camselector").items.each(function(i){
                   i.setValue(true);
              });
          }
      }]
 },{
	  region : 'center',
      xtype       : 'panel',
       autoScroll : true,
       items: [{
           xtype       : 'dataview',
          store        : imagestore,
          itemSelector : 'div.thumb-wrap',
          autoHeight   : true,
          overItemCls  : 'x-view-over',
          emptyText    : "No Images Loaded or Selected for Display",
          tpl          : tpl
       }],
       tbar : [{
           xtype         : 'button',
           text          : 'Help',
           handler       : function() {
               helpWin.show();
           }        
       },{
           xtype         : 'tbtext',
           text          : 'Sort By:'
       },{
           xtype         : 'combo',
           id            : 'sortSelect',
           triggerAction : 'all',
           width         : 80,
           editable      : false,
           mode          : 'local',
           displayField  : 'desc',
           valueField    : 'name',
           lazyInit      : false,
           value         : 'name',
           store         : new Ext.data.ArrayStore({
                fields: ['name', 'desc'],
                data : [['name', 'Name'],['county', 'County'],['cid', 'Camera ID']]
            }),
            listeners: {
                'select': function(sb){
                   imagestore.sort(sb.getValue(), "ASC");
                 }
            }
       },{
           xtype         : 'combo',
           id            : 'networkSelect',
           triggerAction : 'all',
           width         : 100,
           editable      : false,
           mode          : 'local',
           displayField  : 'desc',
           valueField    : 'name',
           lazyInit      : false,
           value         : 'name',
           store         : new Ext.data.ArrayStore({
                fields: ['name', 'desc'],
                data : [['IDOT', 'Iowa DOT RWIS'],
                        ['KCCI', 'KCCI-TV Des Moines'],
                        ['KCRG', 'KCRG-TV Cedar Rapids'],
                        ['KELO', 'KELO-TV Sioux Falls']]
            }),
            listeners: {
                'select': function(sb){
                   imagestore.isLoaded = false;
                   ts = Ext.Date.format(Ext.getCmp("datepicker").getValue(), 'm/d/Y') 
                     +" "+ Ext.getCmp("timepicker").getRawValue();
                   var dt = new Date(ts);
                   if (Ext.getCmp("timemode").realtime){ ts = 0; }
                   else{ ts = Ext.Date.format(dt.toUTC(), 'YmdHi'); }
                   imagestore.reload({
                     add    : false,
                     params : {'ts': ts,
                      'network': Ext.getCmp("networkSelect").getValue() }
                   });
                   window.location.href = "#"+ Ext.getCmp("networkSelect").getValue() +"-"+ ts;
                 }
            }

       },{
          xtype     : 'tbseparator'
       },{
          xtype     : 'button',
          id        : 'timemode',
          text      : 'Real Time Mode',
          realtime  : true,
          handler   : function() {
              if (this.realtime) {
                  Ext.getCmp("datepicker").enable();
                  Ext.getCmp("timepicker").enable();
                  this.setText("Archived Mode");
                  this.realtime = false;
              } else {
                  Ext.getCmp("datepicker").disable();
                  Ext.getCmp("timepicker").disable();
                  this.setText("Real Time Mode");
                  this.realtime = true;
                  imagestore.isLoaded = false;
                  imagestore.reload({add : false, params: {
                     'network': Ext.getCmp("networkSelect").getValue() } });
                   window.location.href = "#"+ Ext.getCmp("networkSelect").getValue() +"-0";
              }
          }
       },{
          xtype     : 'datefield',
          id        : 'datepicker',
          maxValue  : new Date(),
          emptyText : 'Select Date',
          minValue  : '07/23/2003',
          value     : new Date(),
          disabled  : true,
          listeners : {
              select : function(field, value){
                  imagestore.isLoaded = false;
                  ts = Ext.Date.format(Ext.getCmp("datepicker").getValue(), 'm/d/Y') 
                     +" "+ Ext.getCmp("timepicker").getRawValue();
                  var dt = new Date(ts);
                  imagestore.reload({
                      add    : false,
                      params : {'ts': Ext.Date.format(dt.toUTC(), 'YmdHi'),
                        'network': Ext.getCmp("networkSelect").getValue() }
                  });
                  window.location.href = "#"+ Ext.getCmp("networkSelect").getValue() +"-"+ Ext.Date.format(dt.toUTC(), 'YmdHi');
              }
          }
       },{
          xtype     : 'timefield',
          allowBlank: false,
          increment : 1,
          width     : 100,
          emptyText : 'Select Time',
          id        : 'timepicker',
          value     : new Date(),
          disabled  : true,
          listeners : {
              select : function(field, value){
                  imagestore.isLoaded = false;
                  ts = Ext.Date.format(Ext.getCmp("datepicker").getValue(),
                		  'm/d/Y') 
                     +" "+ Ext.getCmp("timepicker").getRawValue();
                  var dt = new Date(ts);
                  imagestore.reload({
                      add    : false,
                      params : {'ts': Ext.Date.format(dt.toUTC(), 'YmdHi'),
                          'network': Ext.getCmp("networkSelect").getValue() }
                  });
                   window.location.href = "#"+ Ext.getCmp("networkSelect").getValue() +"-"+ Ext.Date.format(dt.toUTC(), 'YmdHi');
              }
          }
       }
       ]
    }]
});


var task = {
  run: function(){
    if (imagestore.data.length > 0 && Ext.getCmp("timemode") &&
        Ext.getCmp("timemode").realtime){
      //imagestore.fireEvent('datachanged');
      imagestore.reload({
       add: false, params : {'network': Ext.getCmp("networkSelect").getValue()}
   		});
    }
  },
  interval: cfg.refreshint
};
Ext.TaskManager.start(task);



Ext.namespace('app');
app.appSetTime = function(s){
 if (s.length == 17){ 
    var tokens2 = s.split("-");
    var network = tokens2[0];
    Ext.getCmp("networkSelect").setValue( network );
    var tstamp = tokens2[1];
    var dt = Ext.Date.parseDate(tstamp, 'YmdHi');
    Ext.getCmp("datepicker").setValue( dt.fromUTC() );
    Ext.getCmp("timepicker").setValue( dt.fromUTC() );
    Ext.getCmp("datepicker").enable();
    Ext.getCmp("timepicker").enable();
    Ext.getCmp("timemode").setText("Archived Mode");
    Ext.getCmp("timemode").realtime = false;
    imagestore.isLoaded = false;
    imagestore.reload({
        add    : false,
        params : {'ts': Ext.Date.format(dt, 'YmdHi'),
         'network': Ext.getCmp("networkSelect").getValue() }
    });
    window.location.href = "#"+ Ext.getCmp("networkSelect").getValue() +"-"+ Ext.Date.format(dt, 'YmdHi');
} else if (s.length == 6){ 
   var tokens2 = s.split("-");
   Ext.getCmp("networkSelect").setValue( tokens2[0]);
   imagestore.load({
       add: false, params : {'network': tokens2[0]}
   });
} else {
   imagestore.load();
   Ext.getCmp("networkSelect").setValue("KCCI");
}
};


var tokens = window.location.href.split('#');
if (tokens.length == 2){
  app.appSetTime(tokens[1]);
} else {
   imagestore.load();
   Ext.getCmp("networkSelect").setValue("KCCI");
}

});
