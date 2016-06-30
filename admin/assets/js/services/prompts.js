dah.service('Prompts',function() {

    this.data = {};
    this.count = {"count": 0};

    var data = this.data;
    var count = this.count;

    this.open_prompt = function(name) {
        data[name] = true;
        count.count = count_prompts();
    }

    this.close_prompt = function(name) {
        data[name] = false;
        count.count = count_prompts();
    }

    count_prompts = function() {
        // Trying to find any prompt that is currently open
        for (i in data) {
            if (data[i] == true) {
                return true;
            }
        }
    }

});
