/**
 * object for keeping track of url parameters, makes life easier
 */
var urlVars = {
    year : 2018,
    term : "SUMMER",
    toString : function() {
        return "?year=" + this.year + "&term=" + this.term; 
    },
    navigate : function() {
        window.location.assign(this.toString());
    },
    setYear : function(y) {
        this.id = 0;
        this.year = y;
        this.navigate();
    },
    setTerm : function(t) {
        this.id = 0;
        this.term = t;
        this.navigate();
    }
}

/**
 * Populate the progress table with the specified data
 * 
 * @param {object} data - an object in the following format:
 *   {
 *      "OL": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "OL1": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "OL2": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "HY": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "total: {"unstarted": int, "inprogress": int, "completed": int, "total": int}
 *   }
 */
function populateTable(data) {
    // get outer indexes
    rowNames = Object.keys(data);

    for (row in rowNames) {
        rowName = rowNames[row];

        // get inner indexes
        colNames = Object.keys(data[rowName]);

        for (col in colNames) {
            colName = colNames[col];
            
            // Set content of table
            td =document.querySelector(`[row="${rowName}"][col="${colName}"]`);
            td.innerHTML = data[rowName][colName].toString();
        }
    }

    let completePercentage = 0;
    let inprogressPercentage = 0;

    // calculate percentages to be represented by progress bar
    if (data.total.total != 0) {
        completePercentage = (data.total.completed / data.total.total) * 100;
        inprogressPercentage = (data.total.inprogress / data.total.total) * 100;
    }

    // set width of progress bar
    document.getElementById("complete-bar").style.width = completePercentage.toString() + "%";
    document.getElementById("inprogress-bar").style.width = inprogressPercentage.toString() + "%";
}


/**
 * toggle #show_all button and populate table with specified data
 * 
 * @param {object} data - an object in the following format:
 *   {
 *      "OL": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "OL1": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "OL2": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "HY": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "total: {"unstarted": int, "inprogress": int, "completed": int, "total": int}
 *   }
 */
function showAll(data) {
    document.getElementById("show_all").classList.add("btn-success");
    document.getElementById("show_distinct").classList.remove("btn-success");

    populateTable(data);
}


/**
 * toggle #show_distint button ad populate table with specified data
 * 
 *@param {object} data - an object in the following format:
 *   {
 *      "OL": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "OL1": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "OL2": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "HY": {"unstarted": int, "inprogress": int, "completed": int, "total": int},
 *      "total: {"unstarted": int, "inprogress": int, "completed": int, "total": int}
 *   } 
 */
function showDistinct(data) {
    document.getElementById("show_all").classList.remove("btn-success");
    document.getElementById("show_distinct").classList.add("btn-success");

    populateTable(data);
}


/**
 * Display examity tab and hide other tabs
 */
function showExamity() {
    document.getElementById("examity_tab").classList.add("active");
    document.getElementById("ally_tab").classList.remove("active");
    document.getElementById("sandbox_tab").classList.remove("active");

    document.getElementById("examity_courses").classList.add("active");
    document.getElementById("ally_courses").classList.remove("active");
    document.getElementById("sandbox_courses").classList.remove("active");
}


/**
 * Display ally tab and hide other tabs
 */
function showAlly() {
    document.getElementById("examity_tab").classList.remove("active");
    document.getElementById("ally_tab").classList.add("active");
    document.getElementById("sandbox_tab").classList.remove("active");

    document.getElementById("examity_courses").classList.remove("active");
    document.getElementById("ally_courses").classList.add("active");
    document.getElementById("sandbox_courses").classList.remove("active");
}


/**
 * Display sandbox tab and hide other tabs
 */
function showSandbox() {
    document.getElementById("examity_tab").classList.remove("active");
    document.getElementById("ally_tab").classList.remove("active");
    document.getElementById("sandbox_tab").classList.add("active");

    document.getElementById("examity_courses").classList.remove("active");
    document.getElementById("ally_courses").classList.remove("active");
    document.getElementById("sandbox_courses").classList.add("active");
}


/**
 * Set file label text to the name of a file
 * 
 * @param {string} text - path of file 
 */
function setFileLabel(text) {
    // if text is a path, only use the filename at the end of the path
    let fileName = text.substr(text.lastIndexOf("\\") + 1);
    document.getElementById("file_label").innerHTML = fileName;
}