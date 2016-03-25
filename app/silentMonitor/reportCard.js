/*
 * Name: reportCard.js
 * Application: Silent Monitor
 * Site: psp.byu.edu
 * Author: Joshua Terrasas
 *
 * Description: This is the main JavaScript file for the Silent Monitor Report
 * Card. Most, if not all, of the functionality is done in this document.
 * jQuery is heavily relied upon for most of the user interface.
 */

var report = new ReportCard();

window.onload = function()
{
	var endDate = new Date();
	var startDate = new Date(endDate.getFullYear() - 1, endDate.getMonth());
	var startDatePicker = document.getElementById("startDate");
	var endDatePicker = document.getElementById("endDate");

	startDatePicker.onchange = function()
	{
		report.getData(this.value, endDatePicker.value);
	}

	endDatePicker.onchange = function()
	{
		report.getData(startDatePicker.value, this.value);
	}

	$(startDatePicker).datepicker(
	{
		dateFormat: "yy-mm-dd",
		onSelect: function()
		{
			$(this).change();
		}
	});

	$(endDatePicker).datepicker(
	{
		dateFormat: "yy-mm-dd"
	});

	startDatePicker.value = startDate.format("yyyy-mm-dd");
	endDatePicker.value = endDate.format("yyyy-mm-dd");

	report.getData(startDatePicker.value, endDatePicker.value);
}

// Controller
function ReportCard()
{
	this.data = [];
	this.view = new SilentMonitorCard();
	this.screen = document.getElementById("container");
}

ReportCard.prototype.createReportCard = function()
{
	for(var i = 0; i < this.data.length; i++)
	{
		this.view.addSilentMonitor(this.data[i]);
	}
}

ReportCard.prototype.clear = function()
{
	while(this.data.length > 0)
	{
		this.data.pop();
	}

	while(this.screen.firstChild)
	{
		this.screen.removeChild(this.screen.firstChild);
	}

	this.view = new SilentMonitorCard();
}

ReportCard.prototype.getData = function(startDate, endDate)
{
	$.get("reportCardLoadData.php?startDate=" + startDate + "&endDate=" + endDate, function (data)
	{
		var data = JSON.parse(data);

		report.screen = document.getElementById("container");
		
		if(data.length == 0)
		{
			var noResults = document.createElement("h2");

			noResults.id = "noResults";
			noResults.appendChild(document.createTextNode("No Results Found"));

			report.clear();

			report.screen.appendChild(noResults);

			return;
		}

		if(report.data.length != 0 || report.screen.firstChild)
		{
			report.clear();
		}
		
		for(var i = 0; i < data.length; i++)
		{
			var newSilentMonitor = new SilentMonitor();
			newSilentMonitor.setSMID(data[i].index);
			newSilentMonitor.setDate(data[i].submitDate);
			newSilentMonitor.setComments(data[i].overallComment);

			for(var j = 0; j < data[i].calls.length; j++)
			{
				var newCall = new Call();
				newCall.setDate(data[i].calls[j].date);
				newCall.setComments(data[i].calls[j].comments);
				newCall.setRating(Number(data[i].calls[j].rating));
			
				for(var k = 0; k < data[i].calls[j].criteria.length; k++)
				{
					var newCriterion = new Criterion();
					newCriterion.setTitle(data[i].calls[j].criteria[k].title);
					newCriterion.setDescription(data[i].calls[j].criteria[k].contents);
					newCriterion.setRating(data[i].calls[j].criteria[k].rating.toLowerCase());

					newCall.criteria.push(newCriterion);
				}
	
				newSilentMonitor.calls.push(newCall);
			}

			report.data.push(newSilentMonitor);			
		}

		report.createReportCard();
		report.view.draw(report.screen);
	});
}

// Model

// Silent Monitor Class
function SilentMonitor()
{
	this.SMID;
	this.date = "";
	this.comments = "";
	this.calls = [];
}

SilentMonitor.prototype.setSMID = function(newSMID)
{
	this.SMID = newSMID;
}

SilentMonitor.prototype.setDate = function(newDate)
{
	this.date = newDate;
}

SilentMonitor.prototype.setComments = function(newComments)
{
	this.comments = newComments;
}

// Call Class
function Call()
{
	this.date = "";
	this.comments = "";
	this.rating;
	this.criteria = [];
}

Call.prototype.setDate = function(newDate)
{
	this.date = newDate;
}

Call.prototype.setComments = function(newComments)
{
	this.comments = newComments;
}

Call.prototype.setRating = function(newRating)
{
	this.rating = newRating;
}

Call.prototype.addCriterion = function(newCriterion)
{
	this.criteria.push(newCriterion);
}

// Criteria Class
function Criterion()
{
	this.title = "";
	this.description = "";
	this.rating;
}

Criterion.prototype.setTitle = function(newTitle)
{
	this.title = newTitle;
}

Criterion.prototype.setDescription = function(newDescription)
{
	this.description = newDescription;
}

Criterion.prototype.setRating = function(newRating)
{
	this.rating = newRating;
}

// View

function InformationBar()
{
	this.head = document.createElement("h3");
	this.body = document.createElement("div");
}

function SilentMonitorCard()
{
	this.card = document.createElement("div");
	this.grades = [];
}

SilentMonitorCard.prototype.addSilentMonitor = function(silentMonitor)
{
	var grade = new InformationBar();

	var date = document.createElement("span");
	var score = document.createElement("progress");

	var comments = document.createElement("p");
	var calls = new CallCard();

	date.appendChild(document.createTextNode('Monitor Date: ' + silentMonitor.date));

	comments.appendChild(document.createTextNode('Comments: ' + silentMonitor.comments));

	for(var i = 0; i < silentMonitor.calls.length; i++)
	{
		calls.addCall(silentMonitor.calls[i]);
		calls.card.appendChild(calls.grades[i].head);
		calls.card.appendChild(calls.grades[i].body);
		score.max += calls.grades[i].head.childNodes[1].max;
		score.value += silentMonitor.calls[i].rating;
	}

	score.max--;

	$(calls.card).accordion({active: false, collapsible: true, heightStyle: "content"});

	grade.head.appendChild(date);
	grade.head.appendChild(score);

	grade.body.appendChild(comments);
	grade.body.appendChild(calls.card);

	this.grades.push(grade);
}

SilentMonitorCard.prototype.draw = function(container)
{
	for(var i = 0; i < this.grades.length; i++)
	{
		this.card.appendChild(this.grades[i].head);
		this.card.appendChild(this.grades[i].body);
	}

	$(this.card).accordion({active: false, collapsible: true, heightStyle: "content"});

	container.appendChild(this.card);
}

function CallCard()
{
	this.card = document.createElement("div");
	this.grades = [];
}

CallCard.prototype.addCall = function(call)
{
	var grade = new InformationBar();

	var date = document.createElement("span");
	var score = document.createElement("progress");

	var comments = document.createElement("p");
	var criteria = document.createElement("div");
	var key = document.createElement("div");
	var ratingYes = document.createElement("span");
	var ratingNo = document.createElement("span");
	var ratingPartial = document.createElement("span");
	var ratingUnknown = document.createElement("span");

	date.appendChild(document.createTextNode('Call Date: ' + call.date));
	score.max = 5;
	score.value = call.rating;

	comments.appendChild(document.createTextNode('Comments: ' + call.comments));
	criteria.appendChild(this.getCriteriaTable(call.criteria));

	key.id = "key";
	ratingYes.className += " ui-icon keyItem ui-icon-circle-check icon-green";
	ratingNo.className += " ui-icon keyItem ui-icon-circle-close icon-red";
	ratingPartial.className += " ui-icon keyItem ui-icon-circle-minus icon-yellow";
	ratingUnknown.className += " ui-icon keyItem ui-icon-help icon-orange";
	key.appendChild(ratingYes);
	key.appendChild(document.createTextNode(" = Yes "));
	key.appendChild(ratingNo);
	key.appendChild(document.createTextNode(" = No "));
	key.appendChild(ratingPartial);
	key.appendChild(document.createTextNode(" = Partial "));
	key.appendChild(ratingUnknown);
	key.appendChild(document.createTextNode(" = Unknown"));

	grade.head.appendChild(date);
	grade.head.appendChild(score);

	grade.body.appendChild(comments);
	grade.body.appendChild(criteria);
	grade.body.appendChild(key);

	this.grades.push(grade);
}

CallCard.prototype.getCriteriaTable = function(criteria)
{
	var table = document.createElement("table");
	var titleHeader = document.createElement("th");
	var descriptionHeader = document.createElement("th");
	var ratingHeader = document.createElement("th");

	titleHeader.appendChild(document.createTextNode('Criteria'));
	table.appendChild(titleHeader);

	descriptionHeader.appendChild(document.createTextNode('Description'));
	table.appendChild(descriptionHeader);

	ratingHeader.appendChild(document.createTextNode('Rating'));
	table.appendChild(ratingHeader);

	for(var i = 0; i < criteria.length; i++)
	{
		var row = document.createElement("tr");
		var title = document.createElement("td");
		var description = document.createElement("td");
		var rating = document.createElement("td");
		var ratingIcon = document.createElement("span");

		title.appendChild(document.createTextNode(criteria[i].title));
		description.appendChild(document.createTextNode(criteria[i].description));

		ratingIcon.className += "ui-icon center";

		if(criteria[i].rating == "yes")
		{
			ratingIcon.className += " ui-icon-circle-check icon-green";
			ratingIcon.title = "Yes";
		}
		else if(criteria[i].rating == "no")
		{
			ratingIcon.className += " ui-icon-circle-close icon-red";
			ratingIcon.title = "No";
		}
		else if(criteria[i].rating == "partial")
		{
			ratingIcon.className += " ui-icon-circle-minus icon-yellow";
			ratingIcon.title = "Partial";
		}
		else
		{
			ratingIcon.className += " ui-icon-help icon-orange";
			ratingIcon.title = "Undefined";
		}

		rating.appendChild(ratingIcon);

		row.appendChild(title);
		row.appendChild(description);
		row.appendChild(rating);

		table.appendChild(row);
	}
	
	

	return table;
}
