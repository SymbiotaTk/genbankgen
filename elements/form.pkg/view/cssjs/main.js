// initialize main form
var formLabel = "frm_sequence";
var btnGenerate = "generate";
var btnFileManager = "open_file_manager";

var showHidePubAuthors = document.getElementById("is_published");
var pubAttributesForm = document.getElementById("publication_attributes");
var showSequenceCollection = document.getElementById("sequences");
var sequenceForm = document.getElementById(formLabel);
var btnGenerateObj = document.getElementById(btnGenerate);
var btnFileManagerObj = document.getElementById(btnFileManager);

sequenceForm.elements['username'].value = _gb_config.currentUser;
sequenceForm.elements['user_id'].value = _gb_config.currentUserId;
sequenceForm.elements['record_id'].value = _gb_config.currentRecordId;
sequenceForm.elements['username'].readOnly = true;
sequenceForm.elements['record_id'].readOnly = true;

// testing data
// sequenceForm.elements['sequence_definition'].value = "internal transcribed spacer 1";
// sequenceForm.elements['sequencing_technology'].value = "454";
// sequenceForm.elements['sequence'].value = ">Junk\ngctacgatcgatcgatcgatcgatgctacgtacgtacgatcgatcgtagctag";
// showSequenceCollection.innerHTML = "Display collected items here.";

var collectionTemplate = {
    "columns": [
        { "label": "",     "style": "display: table-cell;", "class": "element_visit",  "value": {
             "type": "a",  "style": "display: table-cell;", "class": "viewadd",
             "title": "View", "href": "javascript:viewSequence('{%__element_index%}');",   "label": ""
        } },
        { "label": "id",   "style": "display: table-cell;", "class": "element_id",     "value": "{%record_id%}" },
        { "label": "name", "style": "display: table-cell;", "class": "element_name",   "value": "{%username%}"},
        { "label": "seq",  "style": "display: table-cell;", "class": "element_seq",    "value": "{%sequence%}..."},
        { "label": "",     "style": "display: table-cell;", "class": "element_delete", "value": {
             "type": "a",  "onclick": "deleteSequence(\'{%__element_index%}\')", "class": "deladd",
             "title": "Delete",       "href": "#", "label": "", "value": {
               "label": "", "class": "tooltip", "type": "span", "value": "Click to delete"
             }
        } },
    ],
};

var browserStorage = new handleLocalStorage({
    "label": "collected_items",
    "output": "sequences",
    "columns": [
      "username", "record_id", "sequence",
      "sequence_definition", "sequencing_technology",
      "user_id", "is_published", "reference",
      "additional_authors", "has_embargo"
    ],
    "format": {},
    "engine": new collectionAddTable(collectionTemplate),
});

var Request = {
    "interface": browserStorage,
    "currentUser": _gb_config.currentUser,
    "currentUserId": _gb_config.currentUserId,
    "currentOccid": _gb_config.currentRecordId,
    // "currentFormValues": {},                // ?

    // "create_url": "{%rurl_create%}",
    // "listing_url": "{%rurl_generated%}",

    "get_id": function() { return this.interface.label; },
    "get_form": function() { return document.getElementById(this.interface.label); },
    "get_fields": function() { return this.interface.columns; },
    "get_current_user": function() { return this.currentUser; },
    "get_current_userid": function() { return this.currentUserId; },
    "get_current_occid": function() { return this.currentOccid; },
}

function addSequence() {
    var fmAddSequence = new formAddSequence_schema();
    var validated = fmAddSequence.confirm(sequenceForm);

    if (validated) {
        browserStorage.save(validated);
        var items = browserStorage.read();
    }
    // [ ] reset form: maintain username, record_id, user_id
    sequenceForm.reset();
    sequenceForm.elements['username'].value = _gb_config.currentUser;
    sequenceForm.elements['user_id'].value = _gb_config.currentUserId;
    sequenceForm.elements['record_id'].value = _gb_config.currentRecordId;
}

function deleteSequence(id) {
    browserStorage.delete(id);
}

function viewSequence(id) {
    var items = browserStorage.read();
    alert(JSON.stringify(items[id]));
}

function processCollection() {
    // alert("Generating sqn files.");
    var req = {
        "items": browserStorage.read(),
        "user_meta_data_url": _gb_config.userMetaDataUrl,
        "specimen_meta_data_url": _gb_config.specimenMetaDataUrl,
        "create_url": _gb_config.createUrl,
    }
    var processing = new handleTbl2asnSubmission(req);
    var result = processing.run();

    // console.log(document.querySelector("#sequenceForm #siteName").value);
    alert("Your submission files have been generated. Please use the Open File Manager button to view the results.");

}

function openFileManager() {
    window.open(_gb_config.fileManagerUrl,"_blank");
}

sequenceForm.setAttribute('action', 'javascript:addSequence();');
btnGenerateObj.setAttribute('onclick', 'javascript:processCollection();');
btnFileManagerObj.setAttribute('onclick', 'javascript:openFileManager();');

function showHideChecked(p,e) {
    e.style.display = "none";
    if (p.checked) { e.style.display = "inline"; }
}

pubAttributesForm.style.display = "none";
showHidePubAuthors.addEventListener('change',
    function (event) { showHideChecked(this, pubAttributesForm); }
);

// initialize addAuthors form
var fmAddAuthors = new HtmlFormEmbedded();
var fmAddAuthors_schema = new formAddAdditionalAuthors_schema();
fmAddAuthors.config = {
    "additional_pretext": "List of additional authors: ",
    "callback_prefix": "deleteAdditionalAuthor",
    "style_class": "delAuthor",
    "container_id": "add_authors",
    "display_template": "{%FirstName%} [[{%MiddleInitial%}]] {%LastName%} ",
    "form_type_class": "HtmlUl_withDelete",
    "form_engine_class": "HtmlFormContainer",
    "form_name": "frm_add_authors",
    "form_schema": fmAddAuthors_schema.schema(),
    "help_text": "",
    "hidden_var_id": "additional_authors",
    "pretext": "Publication authors:",
    "results_container_id": "show_authors",
    "submit_label": "Add Author",
    "validation": fmAddAuthors_schema,
};

fmAddAuthors.init();
function deleteAdditionalAuthor(index) { fmAddAuthors.delete(index); }
fmAddAuthors.display();
browserStorage.read();
