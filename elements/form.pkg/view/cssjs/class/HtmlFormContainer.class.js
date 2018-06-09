// generic form container
var __class = function(e){
  this.id = e.id;
  this.required = {};
  this.node = {};
  this.submit = {};
  this.errors = {};

  this._init();
}

__class.prototype._createElement = function(type) {
  return document.createElement(type);
}

__class.prototype._init = function() {
  this.node = this._createElement("form");
  this.node.id = this.id;
}

__class.prototype.addInputs = function(data) {
  if (data.inputs != undefined) {
      for (input in data.inputs) {
          if (data.inputs.hasOwnProperty(input)) {
              var d = data.inputs[input];
              var item = this._createElement(d.tag);
              item.type = d.type;
              item.placeholder = d.placeholder;
              item.name = d.name;

              item = this._addInputStyle(item, d);
              item = this._addInputRequired(item, d);

              this._addInputNode(item);
          }
      }
  }
}

__class.prototype._addInputStyle = function(item, data) {
  if (data.style != undefined) {
      for (style in data.style) {
          item.style[style] = data.style[style];
      }
  }
  return item;
}

__class.prototype._addInputRequired = function(item, data) {
  if (data.required != undefined) {
      this.required[data.name] = data.required;
  }
  return item;
}

__class.prototype._addInputNode = function(data) {
  return this.node.appendChild(data);
}

__class.prototype.createButton = function(context, func, name) {
  var button = this._createElement("button");
  button.type = "button";
  button.value = name;
  button.onclick = func;
  button.setAttribute("class", "btn btn-primary");
  button.innerHTML = name;
  context.appendChild(button);
}

__class.prototype._allow_null = function(name) {
  if (this.required[name] != undefined) {
    if (this.required[name].validate != undefined) {
        var v = this.required[name].validate;
        if (v.indexOf("not_null") > -1) {
            return false;
        }
    }
  }
  return true;
}

__class.prototype._set_error_msg = function(name) {
  if (this.required[name] != undefined) {
    if (this.required[name].error_msg != undefined) {
        this.current_error_msg = this.required[name].error_msg;
    }
  }
}

__class.prototype._not_valid = function(item, func) {
  // else prompt required.error_msg; and exit; highlight required element
  if (! this._allow_null(item.name)) {
      if (item.value.trim() == "") {
          this._set_error_msg(item.name);
          return true;
      }
  }
  // if valid, run func()
  func(item.name, item.value);
  return false;
}

__class.prototype.reset = function(e, values) {
  for (item in values) {
      if (values.hasOwnProperty(item)) {
          if (values[item].type != "button") {
              var my_item = values[item];
              my_item.value = "";
          }
      }
  };
}

__class.prototype.process = function(e, values) {
  for (item in values) {
      if (values.hasOwnProperty(item)) {
          if (values[item].type != "button") {
              var my_item = values[item];
              var submit = this.submit;
              if (this._not_valid(my_item, function(key, value) { if (submit != undefined) { submit[key] = value; } })){
                  this.errors[my_item.name] = this.current_error_msg;
              }
          }
      }
  };
  if (Object.keys(this.errors).length > 0) {
      var msg = "";
      for (error in this.errors) {
          if (this.errors.hasOwnProperty(error)) {
              msg += this.errors[error]+"\n";
          }
      }
      this.errors = {};
      alert(msg);
      return false;
  }
  return true;
}

var HtmlFormContainer = __class;
