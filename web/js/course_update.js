/**
 * object for keeping track of url parameters, makes life easier
 */
var urlVars = {
    id : 0,
    name : "",
    year : 2018,
    term : "SUMMER",
    session : "OL",
    toString : function() {
        return "?id=" + this.id + "&name=" + this.name + 
            "&year=" + this.year + "&term=" + this.term + 
            "&session=" + this.session;
    },
    navigate : function() {
        window.location.assign(this.toString());
    },
    setId : function(newId) {
        this.id = newId;
        this.navigate();
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
    },
    setSession : function(s) {
        this.id = 0;
        this.session = s;
        this.navigate();
    },
    setName : function(n) {
        this.name = n;
        this.navigate();
    }
}

/**
 * Check or uncheck a checkbox and modify its label. Also send an XMLHttpRequest
 * to toggle_check.php for instant database update
 * 
 * @param {String} checkId - id of checkbox
 */
function toggleCheck(checkId) {
    let check = document.getElementById(checkId);
    let allLabels = document.getElementsByTagName("label");
    let labels = []; // labels associated with check
    let action = ''; // 'ADD' or 'REM'

    for (var i = 0; i < allLabels.length; i++) {
        label = allLabels[i];

        if (label.htmlFor == checkId) {
            labels.push(label);
        }
    }

    // Take appropriate action depending on if checkbox was checkd or unchecked
    if (check.checked) {
        // show name label
        labelclass = labels[0].className.replace('d-none', 'd-inline');
        labels[0].className = labelclass;

        // shift description label down
        labelclass = labels[1].className.replace('d-inline', 'd-block');
        labels[1].className = labelclass;

        action = 'ADD';
    } else {
        // hide name label
        labelclass = labels[0].className.replace('d-inline', 'd-none');
        labels[0].className = labelclass;

        // shift description label up
        labelclass = labels[1].className.replace('d-block', 'd-inline');
        labels[1].className = labelclass;

        action = 'REM';
    }

    let name = document.getElementById("username").value;
    let date = new Date().toISOString().substr(0, 10);
    let dateString = date.substr(5, 2) + "/" + date.substr(8, 2) + "/" + 
                     date.substr(0, 4);
    let taskId = parseInt(checkId.substr(-2, 2));
    
    let by = " by ";
    if (name.length == 0) {
        by = "";
    }

    // modify name label
    labels[0].innerHTML = `Completed${by}<span class="name-span">${name}</span>` +
                          ` on <span class="date-span">${dateString}</span>`;
    
    // Send info to toggle_check.php for a database update
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // update the progress bar of selected course
            document.querySelector("a.nav-link.active .progress").innerHTML = this.responseText;
        }
      };
    xhttp.open('POST', 'php/toggle_check.php', true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`action=${action}&course=${urlVars.id}&task=${taskId}&name=${name}&date=${date}`);
}

/**
 * Activate the checklist that should be active and deactivate the checklist
 * that should not be active when condition is toggled yes. Also, send
 * XMLHttpRequest to toggle_conditional.php for instant database update
 * 
 * @param {String} condition - condition name (ie. "has_examity")
 */
function toggleYes(condition) {
    let idYes = condition + "_yes";
    let ulYes = document.getElementById(idYes);
    let idNo = condition + "_no";
    let ulNo = document.getElementById(idNo);

    // activate the yes checklist if it exists
    if (ulYes) {
        ulYes.style.color = "black";
        let checkboxes = ulYes.getElementsByTagName("input");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].disabled = false;
        }
    }

    // deactivate the no checklist if it exists
    if (ulNo) {
        ulNo.style.color = "gray";
        let checkboxes = ulNo.getElementsByTagName("input");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].disabled = true;
        }
    }

    var xhttp = new XMLHttpRequest();
    xhttp.open('POST', "php/toggle_conditional.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`value=1&course=${urlVars.id}&condition=${condition}`);
}

/**
 * Activate the checklist that should be active and deactivate the checklist
 * that should not be active when condition is toggled no. Also, send
 * XMLHttpRequest to toggle_conditional.php for instant database update
 * 
 * @param {String} condition - condition name (ie. "has_examity")
 */
function toggleNo(condition) {
    let idYes = condition + "_yes";
    var ulYes = document.getElementById(idYes);
    let idNo = condition + "_no";
    var ulNo = document.getElementById(idNo);

    // deactivate yes checklist if it exists
    if (ulYes) {
        ulYes.style.color = "gray";
        let checkboxes = ulYes.getElementsByTagName("input");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].disabled = true;
        }
    }

    // activate no checklist if it exists
    if (ulNo) {
        ulNo.style.color = "black";
        let checkboxes = ulNo.getElementsByTagName("input");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].disabled = false;
        }
    }

    var xhttp = new XMLHttpRequest();
    xhttp.open('POST', "php/toggle_conditional.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`value=0&course=${urlVars.id}&condition=${condition}`);
}

/**
 * Send text to set_notes.php for instant database update
 * 
 * @param {String} notes - notes to be stored
 */
function setNotes(notes) {
    var xhttp = new XMLHttpRequest();
    xhttp.open('POST', 'php/set_notes.php', true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`course=${urlVars.id}&notes=${notes}`);
}

/**
 * Collapse a checklist. If it is already collapsed, uncollapse it
 * 
 * @param {String} id - id of checklist
 */
function collapse(id) {
    element = document.getElementById(id);
    
    if (element.classList.contains('collapse')) {
        element.classList.remove('collapse');
    } else {
        element.classList.add('collapse');
    }
}

/**
 * Activate/deactivate conditional checklists
 */
function toggleAll() {
    var selected = document.querySelectorAll('input[type="radio"]:checked');

    for (var i = 0; i < selected.length; i++) {
        if (selected[i].value == "yes") {
            toggleYes(selected[i].name);
        } else {
            toggleNo(selected[i].name);
        }
    }
}