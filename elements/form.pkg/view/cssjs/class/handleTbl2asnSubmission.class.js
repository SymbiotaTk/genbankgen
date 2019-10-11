// process form data collections for submission to tbl2asn
var __class = function (Request) {
    this.req = Request;
    this.items = this.req.items;
    this.user_data = this.req.user_meta_data_url;
    this.record_data = this.req.specimen_meta_data_url;
    this.results = [];
    this.result_processed = [];
}

__class.prototype.post = function(offset) {
    var ajax = new handleAJAX();
    var submit_data = "data="+encodeURIComponent(JSON.stringify(this.results[offset]));
    var response = ajax.post(this.req.create_url, submit_data);
}

__class.prototype.set = function(key, value, args) {
    if (this.results[args.offset] == undefined) {
        this.results[args.offset] = {};
    }
    this.results[args.offset][key] = value;

    if (this.results[args.offset]["user"] != undefined && this.results[args.offset]["record"] != undefined) {
        var my_results = this.results[args.offset];
        my_results["form"] = args.form;
        this.results[args.offset] = this._source_modifiers(my_results);
        console.log("Dump data ...");
        console.log(this.results);
        this.post(args.offset);
    }
}

// formatting & validation

__class.prototype._format_altitude = function(value) {
    // if null return blank. if has number return with trailing 'm'
    if (value == undefined) { return ""; }
    return value.trim()+"m";
}

__class.prototype._format_country = function(data) {
    return [ data.country, [ data.stateprovince, data.county, data.locality ].join(", ") ].join(": ");
}

__class.prototype._format_date = function(date) {
    //  all portal/symbiota eventdates are 0000-00-00
    var myRegexp = /([0-9]+)-([0-9]+)-([0-9]+)/g;
    var match = myRegexp.exec(date);
    if (match) {
	    var year = match[1];
	    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	    var month = months[parseInt(match[2]) - 1];
	    var day = match[3];
	    // var converted = "{year}-{month}-{day}".replace("{year}", year).replace("{month}", month).replace("{day}", day);
	    var converted = "{day}-{month}-{year}".replace("{year}", year).replace("{month}", month).replace("{day}", day);
	    console.log(converted);
	    return converted;
    }
    return date;
}

__class.prototype._format_latlon = function(data) {
    var lat = data.decimallatitude;
    var lon = data.decimallongitude;

    if ( lat == undefined || lon == undefined) { return ""; }
    lat = parseFloat(data.decimallatitude);
    lon = parseFloat(data.decimallongitude);
    var lat_ordinal = " N";
    var lon_ordinal = " E";

    if (lat < 0) {
        lat = lat * -1;
        lat_ordinal = " S";
    }
    lat = lat+lat_ordinal;
    if (lon < 0) {
        lon = lon * -1;
        lon_ordinal = " W";
    }
    lon = lon+lon_ordinal;

    return lat+", "+lon;
}

__class.prototype._sequence_summary = function(seq, gene) {
    var lines = seq.split("\n");
    var first = lines[0];

    var id = first.trim().split(" ")[0];
    while(id.charAt(0) === '>')
    {
        id = id.substr(1);
    }

    lines.shift();
    var seq_length = lines.join("").length;
    var seq_start = 1;
    var seq_end = seq_length;

    var seq_rna = "rRNA";
    if (gene.includes('ribosomal')) {
        seq_rna = "misc_RNA";
    }

    this.sequence_summary = {
        "start": seq_start,
        "end": seq_end,
        "gene": gene,
        "id": id,
        "rna": seq_rna,
    };
}

__class.prototype._format_sequence = function(seq, gene) {
    var lines = seq.split("\n");
    var first = lines[0];
    this._sequence_summary(seq, gene);

    lines[0] = "{first} [gene]={def}".replace("{first}", first).replace("{def}", gene);
    return lines.join("\n");
}

__class.prototype._format_additional_authors_initials = function(data) {
    for(var key in data) {
        var item = data[key];
        item.Initials = "";
        if (item.FirstName != undefined || item.FirstName.trim() != "") {
            var initials = [];
            initials.push(item.FirstName.trim().charAt(0).toUpperCase());
            if (item.MiddleInitial != undefined || item.MiddleInitial.trim() != "") {
                initials.push(item.MiddleInitial.trim().split("."));
            }
            initials.push("");
            item.Initials = initials.join(".");
        }
    }
    return data;
}

__class.prototype._format_additional_authors = function(data) {
    if (data == undefined || data == "") {
        return [];
    }
    try {
        var obj = unescape(data);
        var json = JSON.parse(obj);
        return this._format_additional_authors_initials(json);
    }
    catch(e) {
        console.log(e.message);
    }
    return [];
}

__class.prototype._format_submission_initials = function(item) {
    var FirstNameInitial = item.firstname.charAt(0).toUpperCase();

    if (item.middleinitial != undefined && item.middleinitial.trim() != "") {
        var arr = [ FirstNameInitial, item.middleinitial ];
        item.initials = arr.join(".")+".";
    }
    return item;
}

__class.prototype._source_modifiers = function(data) {
    var form_data = data.form;
    var user_data = this._format_submission_initials(data.user);
    var record_data = data.record['meta-data'];
    var collection_data = data.record['collection-meta-data'];

    var output = {
        "source-modifiers": {
            "altitude": this._format_altitude(record_data.minimumelevationinmeters),
            "collected-by": record_data.recordedby || "",
            "collection-date": this._format_date(record_data.eventdate),
            "country": this._format_country(record_data),
            "host": record_data.verbatimsciname || "",
            "identified-by": record_data.identifiedby || "",
            "isolation-source": record_data.substrate || "",
            "lat-lon": this._format_latlon(record_data),
            "organism": record_data.sciname || "",
            "specimen-voucher": record_data.catalognumber || "",
        },
        "sbt": {
            "LastName": user_data.lastname,
            "FirstName": user_data.firstname,
            "MiddleInitial": user_data.middleinitial || "",
            "Initials": user_data.initials || "",
            "Organization": user_data.institution || "",
            "Department": user_data.department || "",
            "City": user_data.city || "",
            "StateProvince": user_data.state || "",
            "Country": user_data.country || "",
            "AddressStreet": user_data.address || "",
            "Email": user_data.email || "",
            "Fax": false,
            "Phone": user_data.phone || "",
            "PostalCode": user_data.zip || "",
            "PublicationStatus": "unpublished",
            "PublicationAuthors": false,
            "PublicationTitle": form_data.reference || "",
            "SequencingTechnology": form_data.sequencing_technology || "",
            "PortalUrl": data.record['host'] || "",
            "OccurrenceId": record_data.occurrenceid || "",
            "RecordId": record_data.guid || "",
            "InstitutionCode": collection_data.institutioncode || "",
            "CollectionCode": collection_data.collectioncode || "",
            "CatalogNumber": record_data.catalognumber || "",
            "OtherCatalogNumbers": record_data.othercatalognumbers || "",
        },
        "tbl": {
            "ID": false,
            "START": false,
            "END": false,
            "RNA": false,
            "GENE": false,
            "sequence": this._format_sequence(data.form.sequence, data.form.sequence_definition),
        },
        "publication-authors": {
            "labels": ["LastName", "FirstName", "MiddleInitial", "Initials"],
            "data": this._format_additional_authors(data.form.additional_authors),
        },
    }

    output.tbl.ID = this.sequence_summary.id;
    output.tbl.START = this.sequence_summary.start;
    output.tbl.END = this.sequence_summary.end;
    output.tbl.GENE = this.sequence_summary.gene;
    output.tbl.RNA = this.sequence_summary.rna;

    data.sqn = output;
    return data;
}

__class.prototype.run = function() {
    var ajax = new handleAJAX();
    var entries = [];

    for(var key in this.items) {
        var item = this.items[key];

        var user_url = this.user_data+"?c=user&id="+item.user_id;
        var record_url = this.record_data+"?c=object&id="+item.record_id;

        var user = ajax.get(user_url,
            function(error,data,func) {
                func.parent.set("user", data, { "form": func.form, "offset": func.offset });
                return true;
            }, { "parent": this, "form": item, "offset": entries.length });

        var record = ajax.get(record_url,
            function(error,data,func) {
                func.parent.set("record", data, { "form": func.form, "offset": func.offset });
                return true;
            }, { "parent": this, "form": item, "offset": entries.length });

        var my_data = {
            "form": item,
            "user_url": user_url,
            "record_url": record_url,
            "offset": entries.length,
        }
        entries.push(my_data);
    }
    entries = { "items": entries, "results": this.results };
    return entries;
}

var handleTbl2asnSubmission = __class;
