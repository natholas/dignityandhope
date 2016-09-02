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
})
