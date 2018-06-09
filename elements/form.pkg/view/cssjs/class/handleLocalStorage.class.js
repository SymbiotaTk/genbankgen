// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// handleLocalStorage
var __class = function(req){
    this.Request = req;
    this.name = req.label;
    this.output = req.output;
    this.columns = req.columns;
    this.templates = req.format;
    this.rowFormatHead = req.engine.templateHead();
    this.rowFormatRecord = req.engine.templateRow();
};

__class.prototype._fetch = function() {
    return JSON.parse(localStorage.getItem(this.name));
}

__class.prototype._replace_map = function(str, map) {
    for (var search in map) {
      var re = new RegExp(search, "g");
      str = str.replace(re, map[search]);
    }
    return str;
}

__class.prototype._value_wrap = function(name) {
    return "{%"+name+"%}";
}

__class.prototype._value_map = function(values) {
  var map = {};
  var ikey = "__element_index";
  var wrap_func = this._value_wrap;

  this.columns.forEach(function(label) {
    var wrap = wrap_func(label);
    map[wrap] = values[label];
    if (label == "sequence") {
      map[wrap] = values[label].substring(0,12);
    }
  });
  map[wrap_func(ikey)] = values[ikey];

  return map;
}

__class.prototype.read = function() {
  var items = this._fetch();

  // ?? addCollectionTable::build
  // this.preview = addCollectionTable::build
  // this.preview(items);

  var Results = document.getElementById(this.output);

  if (items === undefined || items === null || items.length === 0){
    Results.innerHTML = '';
    return null;
  }
  Results.innerHTML = this.rowFormatHead;

  for (var i = 0; i < items.length; i++){
    // console.log(items[i]);
    // var seq  = items[i].sequence.substring(0,120);;

    items[i].__element_index = i;

    var map  = this._value_map(items[i]);

    Results.innerHTML += this._replace_map(this.rowFormatRecord, map);
  }
  // ?? addCollectionTable::build END


  return items;
}

__class.prototype.save = function(item){
  if(localStorage.getItem(this.name) === null){
    var items = [];
    items.push(item);
    localStorage.setItem(this.name, JSON.stringify(items));
  } else {
    var items = JSON.parse(localStorage.getItem(this.name));
    items.push(item);
    localStorage.setItem(this.name, JSON.stringify(items));
  }
  // this.Request.form.refresh();
  this.read();
}

__class.prototype.delete = function (id){
  var items = this.read();
  if (items != undefined) {
      if (items[id] != undefined) {
          items.splice(id, 1);
      }
      localStorage.setItem(this.name, JSON.stringify(items));
  }
  this.read();
}

var handleLocalStorage = __class;
