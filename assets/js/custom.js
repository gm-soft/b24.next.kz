/**
 * Created by Next on 02.12.2016.
 */


function printContent(id) {
    var printElement = document.getElementById(id);
    var restorePage = document.body.innerHTML;

    document.body.innerHTML = printElement.innerHTML;
    window.print();
    document.body.innerHTML = restorePage;
}

function getBitrixInstance(method, id){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var jsonResponse = JSON.parse(this.responseText);

            jsonResponse = typeof jsonResponse["result"] !== 'undefined' ? jsonResponse["result"] : jsonResponse;
            jsonResponse = typeof jsonResponse["result"] !== 'undefined' ? jsonResponse["result"] : jsonResponse;
            var textWithBreaks = JSON.stringify(jsonResponse).replace(/,/g, ".<br>").replace(/{/g, "{<br>").replace(/}/g, "}<br>");


            //console.log("ajax response: "+JSON.stringify(jsonResponse));

            $('#output').html("<pre>"+textWithBreaks+"</pre>");
        } else {
            console.log("request is error: "+this.status + ". " + this.statusText);
            $('#output').html("<pre>"+this.status + ". " + this.statusText+"</pre>");
        }
    };
    var body = "method="+method;
    if (typeof id !== 'undefined') body += "&id="+id;
    xhr.open("GET", "http://b24.next.kz/rest/request.php?"+body, true);
    xhr.send();
}

function decode(original){
    var fixedstring = decodeURIComponent(escape(original));
    return fixedstring;
}

function encode_utf8(s) {
  return unescape(encodeURIComponent(s));
}

function decode_utf8(s) {
  return decodeURIComponent(escape(s));
}