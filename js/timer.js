function Timer(_interface_, _time_, _countdown_, _message_) {
	this.interface = _interface_;
	this.time = (typeof _time_ === "undefined" ? 0 : Number(_time_));
	this.countdown = (typeof _countdown_ === "undefined" ? false : Boolean(_countdown_));
	this.message = (typeof _message_ === "undefined" ? "" : String(_message_));

	this.timingVar = undefined;
	this.countingTime = -1;
	
	this.Reset();
}



Timer.prototype.SecondToDisplayForm = function(second, _includeMessage_) {
	var includeMessage = (typeof _includeMessage_ == "undefined" ? true : Boolean(_includeMessage_));

	var m = Math.floor(second / 60);
	var s = second % 60;

	return (includeMessage ? this.message : "") + m + ":" + (s < 10 ? "0" : "") + s;
}

Timer.prototype.Reset = function() {
	clearInterval(this.timingVar);

	this.interface.innerHTML = this.SecondToDisplayForm(this.countdown ? this.time : 0);
	this.countingTime = -1;
}

Timer.prototype.Start = function() {
	var intervalFunction = function(timerObject) {
		--timerObject.countingTime;

		if (timerObject.countingTime < 0) {
			clearInterval(timerObject.timingVar);

			if (typeof timerObject.onFinish != "undefined")
				timerObject.onFinish();
		} else 
			timerObject.interface.innerHTML = timerObject.SecondToDisplayForm(
				timerObject.countdown ? timerObject.countingTime : (timerObject.time - timerObject.countingTime)
				);	
	}

	this.countingTime = this.time;
	this.timingVar = setInterval(intervalFunction, 1000, this);
}

Timer.prototype.Stop = function() {
	clearInterval(this.timingVar);
}

Timer.prototype.GetCurrentTime = function() {	
	if (this.countingTime == -1) return -1;

	return this.countdown ? this.countingTime : this.time - this.countingTime;
}

Timer.prototype.onFinish = undefined;