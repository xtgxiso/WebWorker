var express = require('express');
var app = express();
var redis = require("redis");


client = redis.createClient('6379','127.0.0.1');
client.auth("123456");
client.select(1);
client.set("xtgxiso","Hello xtgxiso")

app.get('/', function (req, res) {
    client.get('xtgxiso', function(error, data){
        if(error) {
            console.log(error);
        } else {
            //console.log(data);
        }
        res.send(data);
    });
});

var server = app.listen(1215, function () {
  var host = server.address().address;
  var port = server.address().port;

  console.log('Example app listening at http://%s:%s', host, port);
});
