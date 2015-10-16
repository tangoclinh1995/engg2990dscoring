var timerManagement = new Object();


function UpdateHiddenInput(hiddenInput, newValue) {
	$(hiddenInput).val(newValue)

	var spanElement = $(hiddenInput).parent().find("span")

	if (spanElement.length > 0)
		$(spanElement).html(newValue);
	else
		$(hiddenInput).parent().find("input[type='checkbox']").prop("checked", Boolean(newValue > 0));
}


function StopPenaltyTimer(recordName, buttonTimer, componentRecord) {	
	var totalTime = timerManagement[recordName].GetCurrentTime();

	timerManagement[recordName].Reset();
	timerManagement[recordName] = false;

	$(buttonTimer).html("Start");

	var max = Number($(buttonTimer).parent().find("input.param-max[type='hidden']").val());
	var timerThreshold = Number($(buttonTimer).parent().find("input.param-timerthreshold[type='hidden']").val());
	var value = Number($(componentRecord).val());

	//If Timer automatically stops before user clicks STOP, meaning that it automatically reset itself and time limit is reached
	if (totalTime == -1) totalTime = timerThreshold;	

	UpdateHiddenInput(componentRecord, Math.min(value + Number(totalTime >= timerThreshold), max));
}


function FinishRace() {
	var totalTime = mainTimer.GetCurrentTime();

	//Stop all sub-Timer, if they're running
	$("table#tableScoring").find("button.button-timer").each(function(index) {
		var recordName = $(this).parent().find("input.record[type='hidden']").attr("name");
		
		if (recordName in timerManagement && timerManagement[recordName] !== false)
			$(this).click();
	});

	//Stop the main Timer
	mainTimer.Reset();

	$("button#btnMainTimerStart").html("START");

	var timeLimit = Number($("input[type='hidden']#mainTimerLimit").val());	

	//If Timer automatically stops before user clicks FINISH, meaning that it automatically reset itself and time limit is reached
	if (totalTime == -1) totalTime = timeLimit;

	$("input[type='hidden'][name='time'].record").val(totalTime);

	$("span#spanMainTimer").html(mainTimer.SecondToDisplayForm(totalTime));
}


$("table#tableScoring").find("button.button-increase").on("click", function() {
	var max = Number($(this).parent().find("input.param-max[type='hidden']").val());
	var componentRecord = $(this).parent().find("input.record[type='hidden']");		
	var value = Number($(componentRecord).val());

	if (value + 1 <= max)
		UpdateHiddenInput(componentRecord, value + 1);
});


$("table#tableScoring").find("button.button-decrease").on("click", function() {
	var componentRecord = $(this).parent().find("input.record[type='hidden']");
	var value = Number($(componentRecord).val());

	if (value - 1 >= 0)
		UpdateHiddenInput(componentRecord, value - 1);
});


$("table#tableScoring").find("button.button-timer").on("click", function() {
	var componentRecord = $(this).parent().find("input.record[type='hidden']")
	var recordName = $(componentRecord).attr("name");

	if (!(recordName in timerManagement)
			|| (recordName in timerManagement && timerManagement[recordName] === false)) {
		timerManagement[recordName] = new Timer($(this)[0], 3600, false, "Stop: ");

		var buttonTimer = this;

		timerManagement[recordName].onFinish = function() {
			StopPenaltyTimer(recordName, buttonTimer, componentRecord);	
		}

		timerManagement[recordName].Start();
	} else
		StopPenaltyTimer(recordName, this, componentRecord);
});


$("table#tableScoring").find("td.penalty").find("input[type='checkbox']").on("change", function() {
	var componentRecord = $(this).parent().find("input.record[type='hidden']");
	UpdateHiddenInput(componentRecord, Number($(this).prop("checked")));	
});


$("table#tableScoring").floatThead({
	scrollingTop: 36,
});


var mainTimer = new Timer($("span#spanMainTimer")[0],
							Number($("input[type='hidden']#mainTimerLimit").val()),
							false,
							"&nbsp;&nbsp;&nbsp;");


mainTimer.onFinish = FinishRace;


$("button#btnMainTimerStart").on("click", function() {
	if (mainTimer.GetCurrentTime() == -1) {
		mainTimer.Start();
		$(this).html("FINISH");
	} else
		FinishRace();
});


$("button#btnNewRace").on("click", function() {
	if (confirm("Do you want to reset the form?\nATTENTION: All data will be cleared")) {
		FinishRace();

		var tableScoring = $("table#tableScoring");

		$("span#spanMainTimer").html("&nbsp;&nbsp;&nbsp;0:00");

		tableScoring.find("input[type='text']").val("");
		tableScoring.find("input[type='hidden'].record").val(0);
		tableScoring.find("input[type='checkbox']").prop("checked", false);
		tableScoring.find("td.penalty").find("span").html("0");
	}
});


$("button#btnMainTimerManualSet").on("click", function() {
	//If Main Timer is running, do not allow to manually set time
	if (mainTimer.GetCurrentTime() != -1) return ;

	var input = prompt("Enter the time of the race\nFormat: MM:SS");
	if (input == null) return ;

	var output = /(\d+):(\d+)/.exec(input);
	
	if (output == null) {
		alert("Your input of time is invalid! Please try again");
		return ;
	}

	var inputTime = Number(output[1]) * 60 + Number(output[2]);
	var timerLimit = Number($("input[type='hidden']#mainTimerLimit").val());

	if (inputTime > timerLimit) {
		alert("This time exceeds the time limit of the task. Therefore, the time limit will be used instead.");
		inputTime = timerLimit;
	}

	$("input[type='hidden'][name='time'].record").val(inputTime);
	$("span#spanMainTimer").html(mainTimer.SecondToDisplayForm(inputTime));
});


$("form#formScoring").on("submit", function() {
	var summaryText = "";

	//Validate Team number
	var temp = Number($("input[type='text'][name='team']").val().trim());
	if (temp == NaN || (temp != NaN && (temp <= 0 || temp - Math.floor(temp) != 0))) {
		alert("Invalid Team number!");
		return false;
	} else {
		$("input[type='text'][name='team']").val(temp);
		
		summaryText += "Team: " + temp + "   ";
	}

	//Validate Race number
	temp = Number($("input[type='text'][name='race']").val().trim());
	if (temp == NaN || (temp != NaN && (temp <= 0 || temp - Math.floor(temp) != 0))) {
		alert("Invalid Race number!");
		return false;
	} else {
		$("input[type='text'][name='race']").val(temp);
		
		summaryText += "Race No.: " + temp + "\n";
	}

	//Validate Player name
	temp = $("input[type='text'][name='players']").val().trim().replace("  ", " ");
	if (temp == "") {
		alert("Name of players are not filled in!");
		return false;
	} else {
		$("input[type='text'][name='players']").val(temp);

		summaryText += "Players: " + temp + "\n";
	}

	var totalTime = 0;

	//Create a summary of result + Calculate Total Time
	$(this).find("input[type='hidden'].record").each(function(index) {
		switch ($(this).attr("name")) {
			case "time":
				summaryText += "Time: " + mainTimer.SecondToDisplayForm($(this).val(), false) + "\n";

				totalTime += Number($(this).val());

				break;

			case "total":
				break;

			default:
				var paramWeight = Number($(this).parent().find("input[type='hidden'].param-weight").val());

				summaryText += 
					$(this).parent().parent().find("td.penalty-title").html() + ": " +
					$(this).val()
					+ " (x" + paramWeight + " sec)\n";

				totalTime += paramWeight * Number($(this).val());

				break;
		}
	});

	summaryText += "-----\nTOTAL: " + mainTimer.SecondToDisplayForm(totalTime, false);

	//Put total time to the hidden input in order to submit it to server
	$("input[type='hidden'][name='total'].record").val(totalTime);	

	return confirm(summaryText + "\n\nAre you sure your record is correct?");	
});