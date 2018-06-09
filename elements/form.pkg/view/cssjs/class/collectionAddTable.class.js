// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// collectionAddTable class
var __class = function (template) {
    this.data = template;
    this._div_ = document.createElement('div');
    this._span_ = document.createElement('span');
    this._a_ = document.createElement('a');
}

__class.prototype._getType = function (val) {
    if (typeof val === 'undefined') return 'undefined';
    if (typeof val === 'object' && !val) return 'null';
    return ({}).toString.call(val).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
}

__class.prototype._columnCell = function(data,type) {
    if (type != undefined) {
        if (type == "row") {
            if (this._getType(data.value) == "string") {
                var r = this._span_.cloneNode(false);
                r.setAttribute("style", data.style);
                r.setAttribute("class", data.class);
                r.innerHTML = data.value;
            } else {
                if (data.value.type == "a") {
                    var r = this._a_.cloneNode(false);
                    r.setAttribute("class", data.value.class);
                    r.setAttribute("href", data.value.href);
                    r.innerHTML = data.value.label;
                    if (data.value.onclick != undefined) {
                        r.setAttribute("onclick", data.value.onclick);
                    } else {
                        r.setAttribute("style", data.value.style);
                        r.setAttribute("target", data.value.target);
                    }
                }
            }
            return r;
        }
    }
    var c = this._span_.cloneNode(false);
    c.setAttribute("style", data.style);
    c.setAttribute("class", data.class);
    c.innerHTML = data.label;
    return c;
}

__class.prototype.templateHead = function() {
    var data = this.data;
    var div = this._div_.cloneNode(false);
    div.setAttribute("style", "display: table-row;");
    div.setAttribute("class", "well-head");

    var parent = this;
    data.columns.forEach(function(o) {
        div.appendChild(parent._columnCell(o));
    });

    return div.outerHTML;
}

__class.prototype.templateRow = function() {
    var data = this.data;
    var div = this._div_.cloneNode(false);
    div.setAttribute("style", "display: table-row;");
    div.setAttribute("class", "well");

    var parent = this;
    data.columns.forEach(function(o) {
        div.appendChild(parent._columnCell(o,"row"));
    });

    return div.outerHTML;
}

var collectionAddTable = __class;
