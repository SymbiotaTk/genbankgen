// HTML ul create/delete

var __class = function (e) {
  // displays table of <document.Element>
  // provides delete function by array index
  this.config = e;
}

__class.prototype.clear = function() {
  while (this.config.context.firstChild) {
      this.config.context.removeChild(this.config.context.firstChild);
  }
}

__class.prototype.delete_element = function(index) {
  var a = document.createElement("a");
  a.href = "#";
  a.setAttribute("class", this.config.style_class);
  a.setAttribute("onclick", this.config.callback_prefix+"("+index+");");
  a.innerHTML = "x";
  return a;
}

__class.prototype.delete = function(index) {
  var source = this.config.source;
  if (source.value) {
      var current = source.value.uriDecode();
      current.splice(Number(index),1);
      this.clear();
      source.value = this.uriEncode(current);
      this.display();
  }
  event.preventDefault();
}

__class.prototype.display = function() {
  var data = this.config.source;
  var context = this.config.context;
  var template = this.config.display_template;

  if (data.value != "") {
      data = data.value.uriDecode();

      if (data.keys().length < 1) { return false; };
      context.innerHTML = this.config.pretext;

      var wrapper = document.createElement("ul");

      for (item in data) {
        if (data.hasOwnProperty(item)) {
          data[item] = this.config.validation.confirm(data[item]);
          var li = document.createElement("li");
          li.innerHTML = template.mapReplace(this.mapKeys(data[item]));
          li.appendChild(this.delete_element(item));
          wrapper.appendChild(li);
        }
      }

      context.appendChild(wrapper);
  } else {
      context.innerHTML = this.config.additional_pretext;
  }
}

__class.prototype.store = function(form) {
  var hidden_object = this.config.source;

  var previous = hidden_object.getAttribute("value");
  if (previous == null) {
      previous = [];
  } else {
      previous = previous.uriDecode();
  }
  previous.push(form.submit);
  return this.uriEncode(previous);
}

__class.prototype.uriEncode = function(uri) {
  return encodeURIComponent(JSON.stringify(uri));
}

__class.prototype.mapKeys = function(data) {
    var map = {};
    for (label in data) {
        if (data.hasOwnProperty(label)) {
          if (label.trim() != "") {
            var wrap = "{%"+label+"%}";
            map[wrap] = data[label];
          }
        }
    }
    return map;
}

var HtmlUl_withDelete = __class;
