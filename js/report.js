var TABLE_HEADER_TEMPLATE = "<th title='Click to sort the records based on [HEADER_TITLE]' id='[HEADER_ID]'>[HEADER_TITLE] <span class='spanSort'>[SPAN_SYMPBOL]</span></th>";
var SORT_ASCEND_SYMBOL = "&#x25B2;";
var SORT_DESCEND_SYMBOL = "&#x25BC;";


var sortColumn = "", sortAscend = true;
var filterTeams = "", filterRaces = "", filterGetMin = false;



function LoadTableHeaders(headers) {
	var headerHTML = "";

	for (i = 0; i < headers.length; ++i)
		headerHTML += TABLE_HEADER_TEMPLATE
						.replace(/\[HEADER_TITLE\]/g, headers[i][1])
						.replace(/\[HEADER_ID\]/g, headers[i][0])
						.replace(/\[SPAN_SYMPBOL\]/g, headers[i][0] == sortColumn ?
							(sortAscend ? SORT_ASCEND_SYMBOL : SORT_DESCEND_SYMBOL) : "");

	$("table#tableReport").find("thead").find("tr").html(headerHTML);

	AssignEventsToTableHeaders();
}


function LoadTableRecords(records) {
	var recordHTML = "";

	for (i = 0; i < records.length; ++i) {
		recordHTML += "<tr>";

		for (j = 0; j < records[i].length; ++j)
			recordHTML += "<td>" + records[i][j] + "</td>";

		recordHTML += "</tr>";
	}

	$("table#tableReport").find("tbody").html(recordHTML);
}


function AssignEventsToTableHeaders() {
	$("table#tableReport").find("th").on("click", function() {
		var columnId = $(this).attr("id");

		AJAXReloadRecord($("button#btnFilter"), columnId, sortColumn != columnId ? true : (!sortAscend));
	});
}


function AJAXReloadRecord(filterButton, optionSortColumn, optionSortAscend) {
	$(filterButton).html("Loading ...")
	$(filterButton).prop("disabled", true);
		
	$.ajax({	url: "report_getrecord.php",
				method: "post",
				data: {
					game: GAME_DATABASE,
					team: filterTeams,
					race: filterRaces,
					getmin: Number(filterGetMin),
					sortcol: optionSortColumn,
					sortasc: Number(optionSortAscend)
				},
				dataType: "json",
				success: function(result, status, xhr) {	
					//Reconfigure global sorting requirement
					sortColumn = optionSortColumn;
					sortAscend = optionSortAscend;

					LoadTableHeaders(result[0]);
					LoadTableRecords(result[1]);
				},
				error: function(xhr, status, error) {
					alert("Cannot load the data. Please try again later!");
				},
				complete: function(xhr, status) {
					$(filterButton).html("Refresh");
					$(filterButton).prop("disabled", false);
				}
			});	
}



$("button#btnFilter").on("click", function() {
	//Collect and validate request
	filterTeams = $("input#filterTeam").val();
	filterTeams = typeof filterTeams == "undefined" ? "" : filterTeams.replace(/ /g, "");	

	filterRaces = $("input#filterRace").val();
	filterRaces = typeof filterRaces == "undefined" ? "" : filterRaces.replace(/ /g, "");

	filterGetMin = $("input#checkMin").prop("checked");
	if (typeof filterGetMin == "undefined") filterGetMin = false;

	AJAXReloadRecord(this, "", true);
});



$("table#tableReport").floatThead({
	scrollingTop: 36,
});

AssignEventsToTableHeaders();