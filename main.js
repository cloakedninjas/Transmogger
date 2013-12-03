var connect = require('connect');
connect.createServer(
  connect.static(__dirname + '/public_html')
).listen(8080);
