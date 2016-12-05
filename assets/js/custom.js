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