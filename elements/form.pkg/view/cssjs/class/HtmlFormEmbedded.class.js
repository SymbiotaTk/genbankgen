// Embedded Form

var __class = function() {
    this.config = {
      "additional_pretext": "",
      "callback_prefix": "",
      "container_id": false,
      "display_template": "",
      "form_engine_class": false,
      "form_name": false,
      "form_schema": false,
      "form_type_class": false,
      "help_text": "",
      "hidden_var_id": false,
      "pretext": "",
      "style_class": "",
      "submit_label": false,
      "results_container_id": false,
      "validation": false,
    };
}

__class.prototype.init = function() {
    this.AddContainer = document.getElementById(this.config.container_id);
    this.ShowContainer = document.getElementById(this.config.results_container_id);
    this.hidden_value = document.getElementById(this.config.hidden_var_id);
    this.config.context = this.ShowContainer;
    this.config.source = this.hidden_value;

    // console.log(this.config);
    var form_type = this.config.form_type_class;
    this.form = new window[form_type](this.config);
}

__class.prototype._showHide = function(context) {
    if (context.style.display == 'none' || context.style.display == undefined) {
	context.style.display = 'block';
    } else {
	context.style.display = 'none';
    }
}

__class.prototype._ShowForm = function (context) {
      // console.log(context);
      var engine = this.config.form_engine_class;
      var FormEngine = new window[engine]({"id": this.config.form_name});
      FormEngine.addInputs(this.config.form_schema);
      var form = FormEngine.node;
      var hidden_value = this.hidden_value;
      var display = this.form;
      var showHide_func = this._showHide;
      var help_text = this.config.help_text;

      var button = FormEngine.createButton(form, function(e){
          var check = FormEngine.process(e, this.parentNode.childNodes);
          if (check) {
              hidden_value.value = display.store(FormEngine);
              FormEngine.reset(e, this.parentNode.childNodes);
          }
          if (hidden_value.value) {
              display.display();
          }
      }, this.config.submit_label);

      FormEngine.createButton(context, function(){
          var form = this.parentNode.childNodes[1];
          showHide_func(form);
      }, "+");

      var wrapper = document.createElement("div");
      wrapper.style.display = 'none';

      var text = document.createElement("div");
      text.innerHTML = help_text;

      wrapper.appendChild(text);
      wrapper.appendChild(form);
      context.appendChild(wrapper);
};

__class.prototype.display = function() {
    this._ShowForm(this.AddContainer);
    this.form.display();
}

__class.prototype.delete = function(index) {
      var form_type = this.config.form_type_class;
      var d = new window[form_type](this.config);
      d.delete(index);
}

var HtmlFormEmbedded = __class;

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// GLOBAL SPACE //
mapReplace_plugin_remove_blank = function(str, map, re, search) {
    // find [[{%label%}(...)]]
    var myRegexp = /(?:^|\s)(\[\[.*?\]\])(?:\s|$)/g;
    var optional = myRegexp.exec(str);

    if (optional != undefined) {
        var match = re.exec(optional[1]);

        if (match != undefined) {
            if (map[search].trim() == "") {
                str = str.replace(optional[1], "");
            } else {
                str = str.replace(optional[1], optional[1].substring(2, optional[1].length - 2));
            }
        }
    }
    return str;
}

mapReplace_plugin = function(str, map) {
    for (var search in map) {
      if (map.hasOwnProperty(search)) {
        var str = str;
        var re = new RegExp(search, "g");
        str = mapReplace_plugin_remove_blank(str, map, re, search);
        str = str.replace(re, map[search]);
      }
    }
    return str;
}

if (typeof String.prototype.mapReplace != 'function') { // detect native implementation
    String.prototype.mapReplace = function (map) {
        var str = this;
        return mapReplace_plugin(str, map);
    }
}

if (typeof String.prototype.uriDecode != 'function') { // detect native implementation
    String.prototype.uriDecode = function () {
        return JSON.parse(decodeURIComponent(this));
    }
}

if (typeof String.prototype.trim != 'function') { // detect native implementation
    String.prototype.trim = function () {
        return this.replace(/^\s+/, '').replace(/\s+$/, '');
    };
}
