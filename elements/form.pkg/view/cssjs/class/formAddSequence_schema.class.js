// form schema and validation class: Sequence [main]
// required methods: confirm, schema

var __class = function() {
    this.errors = [];
    this.is_published = false;
}

__class.prototype._error = function(msg) {
    alert(msg);
    return false;
}

__class.prototype._error_add = function(msg) {
    this.errors.push(msg);
    return false;
}

__class.prototype._compile_errors = function(item) {
    var required = this._has_required(item);
    if (required) {
        var methods = Object.getOwnPropertyNames(required);
        if (methods.indexOf("error_msg") != -1) {
            this.errors.push(item.required.error_msg);
        }
    }
}

__class.prototype._errors_exist = function() {
    if (this.errors.length > 0) {
        this._error(this.errors.join("\n"));
        return true;
    }
    return false;
}

__class.prototype._check_pub_status = function(data) {
    this.is_published = data.elements['is_published'].checked;
}

__class.prototype._process = function(data) {
    var valid = ["input","textarea","select"];
    var kv = {};

    if (!this.is_published) {
        this._check_pub_status(data);
    }

    for (i = 0; i < data.length; i++) {
        var element = data.elements[i];
        if (element.id.trim() == "") { continue; }
        if (valid.indexOf(element.tagName.toLowerCase()) != -1){
            this._validate(element.id, element.value);
            kv[element.id] = element.value;
            if (element.type == "checkbox") {
                kv[element.id] = element.checked;
            }
        }
    }
    if (this._errors_exist()) {
        return false;
    }
    return kv;
}

__class.prototype._get_schema_element = function(id) {
    var schema = this.schema();
    return schema.inputs.find(o => o.id === id);
}

__class.prototype._validateFasta = function(text) {
    var schema = this.schema();
    var error_msg = "Please provide a valid FASTA sequence.";
    if (!text) { return this._error_add(error_msg); }
    var seq = text;
    seq = seq.trim();
    var lines = seq.split('\n');
    if (seq[0] == '>') { lines.splice(0, 1); }  // remove first line
    seq = lines.join('').trim();
    if (!seq) { return this._error_add(error_msg); } // if empty
    if (/^[ACDEFGHIKLMNPQRSTUVWY\s]+$/i.test(seq)) { return text; }
    return this._error_add(error_msg);
}

__class.prototype._has_required = function(item) {
    if (item != undefined) {
        var props = Object.getOwnPropertyNames(item);
        if (props.indexOf("required") != -1) {
            return item.required;
        }
    }
    return false;
}

__class.prototype._has_required_methods = function(item) {
    var required = this._has_required(item);
    if (required) {
        var valids = Object.getOwnPropertyNames(required);
        if (valids.indexOf("validate") != -1) {
            return item.required.validate;
        }
    }
    return false;
}

__class.prototype._required_not_blank = function(key, value) {
    var req_not_blank = false;
    var model = this._get_schema_element(key);
    var methods = this._has_required_methods(model);
    if (methods) {
        if (methods.indexOf("not_null") != -1) {
            if (value == undefined || value.trim() == "") {
                return model;
            }
        }
    }
    return req_not_blank;
}

__class.prototype._required_not_blank_if_published = function(key, value) {
    var req_not_blank_if_pub = false;
    if (!this.is_published) { return req_not_blank_if_pub; }
    var model = this._get_schema_element(key);
    var methods = this._has_required_methods(model);
    if (methods) {
        if (methods.indexOf("not_null_if_published") != -1) {
            if (value == undefined || value.trim() == "") {
                return model;
            }
        }
    }
    return req_not_blank_if_pub;
}

__class.prototype._validate = function(key, value) {
    var is_required_blank = this._required_not_blank(key, value);
    if (is_required_blank) {
      this._compile_errors(is_required_blank);
      return false;
    }

    var is_required_blank_if_published = this._required_not_blank_if_published(key, value);
    if (is_required_blank_if_published) {
      this._compile_errors(is_required_blank_if_published);
      return false;
    }

    switch (key) {
      case "sequence":
        if(this._validateFasta(value)) {
            if (value) {
                return value;
            }
        }
        break;

      default:
        return value;
    }
    // [ ] edit border class for CSS
    return false;
}

__class.prototype.confirm = function(data) {
    return  this._process(data);
}

__class.prototype.schema = function() {
    return {
        "inputs": [
          { "id": "username",
            "label": "User Name",
            "required": { "validate": [ "not_null" ],
                          "error_msg": "Please provide a User Name." } },
          { "id": "record_id",
            "label": "Record id",
            "required": { "validate": [ "not_null" ],
                          "error_msg": "Please provide a valid Record id." } },
          { "id": "sequence",
            "label": "Sequence",
            "required": { "validate": [ "not_null" ],
                          "error_msg": "Please provide a valid FASTA Sequence." } },
          { "id": "sequence_definition",
            "label": "Sequence Definition",
            "required": { "validate": [ "not_null" ],
                          "error_msg": "Please select an appropriate Sequence Definition." } },
          { "id": "sequencing_technology",
            "label": "Sequencing Technology",
            "required": { "validate": [ "not_null" ],
                          "error_msg": "Please select an appropriate Sequencing Technology." } },
          { "id": "reference",
            "label": "Title of Publication",
            "required": { "validate": [ "not_null_if_published" ],
                          "error_msg": "Please provide a Title of Publication." } },
          { "id": "additional_authors",
            "label": "Additional authors",
            "required": { "validate": [ "not_null_if_published" ],
                          "error_msg": "Please provide Additional authors for your publication." } },
        ]
    };
}

var formAddSequence_schema = __class;
