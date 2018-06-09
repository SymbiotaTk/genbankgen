// form schema and validation class: Additional Authors
// required methods: confirm, schema

var __class = function() {}

__class.prototype._remove_trailing_char = function(str, char) {
  str.trim();
  var last = str.substring(str.length - 1);
  while (last == char) {
      str = str.substring(0, str.length - 1);
      str.trim();
      last = str.substring(str.length - 1);
  }
  return str;
}

__class.prototype.fix_middleInitialPeriod = function(data) {
    if (data.MiddleInitial != undefined && data.MiddleInitial.trim() != "") {
        data.MiddleInitial = this._remove_trailing_char(data.MiddleInitial, ".") + ".";
    }
    return data;
}

__class.prototype.confirm = function(data) {
    data = this.fix_middleInitialPeriod(data);
    return data;
}

__class.prototype.schema = function() {
    return {
          "inputs": [
            { "tag": "input",
              "type": "text",
              "placeholder": "FirstName",
              "name": "FirstName",
              "style": { "border": "2px solid #dcd", "border-radius": "4px" },
              "required": { "validate": [ "not_null" ],
                            "error_msg": "Please provide a First Name." } },
            { "tag": "input",
              "type": "text",
              "placeholder": "M",
              "name": "MiddleInitial",
              "style": { "width": "40px", "border": "2px solid #dcd", "border-radius": "4px" },
              "required": { "validate": [],
                            "error_msg": "Not required." } },
            { "tag": "input",
              "type": "text",
              "placeholder": "LastName",
              "name": "LastName",
              "style": { "border": "2px solid #dcd", "border-radius": "4px" },
              "required": { "validate": [ "not_null" ],
                            "error_msg": "Please provide a Last Name." } },
          ]
        };
}

var formAddAdditionalAuthors_schema = __class;
