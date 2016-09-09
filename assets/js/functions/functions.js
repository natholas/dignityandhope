Date.prototype.addDays = function(days)
{
    var dat = new Date(this.valueOf());
    dat.setDate(dat.getDate() + days);
    return dat;
}

Date.prototype.addHours = function(hours)
{
   this.setTime(this.getTime() + (hours*60*60*1000));
   return this;
}

String.prototype.capitalizeFirstLetter = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

dah.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});

dah.filter("dobToAge", function() {

    var secondsinyear = 60 * 60 * 24 * 30 * 12;
    return function(dob) {
        var cd = parseInt(new Date().getTime() / 1000);
        return Math.floor((cd - dob) / secondsinyear);
    }
});

dah.filter("convert", function(Currency) {
    return function(amount){
        return amount / Currency.data.currentCurrency.value;
    }
});

dah.filter("upperCase", function () {
    return function (input) {
        return input.toUpperCase();
    }
});

function dobToTimestamp(dob) {
    return new Date(dob.substring(6,10), dob.substring(3,5) -1, dob.substring(0,2)).getTime() / 1000;
}

function timestampToDob(timestamp) {
    var date = new Date(timestamp * 1000);
    return pad_length(date.getDate(), 2) + "/" + pad_length(date.getMonth() + 1, 2) + "/" + date.getFullYear();
}

function pad_length(input, length) {
    input = input + "";
    var x = length - input.length;
    if (input.length < length) {
        for (var i = 0; i < x; i++) {
            input = "0" + input;
        }
    }
    return input;
}
