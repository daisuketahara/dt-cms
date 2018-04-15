
(function($) {

    var label;
    var xml;
    var preview;
    var printButton;
    var autoPrint;
    var printer;

    $.fn.dtDymoBadge = function(options){

        // Default options
        var settings = $.extend({
            xml: '',
            preview: false,
            printButton: false,
            autoPrint: false,
            printer: 'DYMO LabelWriter 450'
        }, options );

        object = this;
        xml = settings.xml;
        preview = settings.preview;
        printButton = settings.printButton;
        autoPrint = settings.autoPrint;
        printer = settings.printer;

        label = dymo.label.framework.openLabelXml(xml);

        if (!label) {

            object.append('Label not loaded!');
            return;
        }

        if (preview) {
            object.append('<img id="dt-dymo-preview" class="img-fluid">');
            var pngData = label.render();
            $('#dt-dymo-preview').attr('src', "data:image/png;base64," + pngData);
        }

        if (printButton) {
            object.append('<a id="dt-dymo-print" class="btn btn-secondary pointer">Print</a>');
        }

        var printers = dymo.label.framework.getPrinters();
        if (printers.length == 0) {
            alert("No DYMO printers are installed. Install DYMO printers.");
            return;
        }

        /*
        for (var i = 0; i < printers.length; i++) {
            var printer = printers[i];
            if (printer.printerType == "LabelWriterPrinter") {
                var printerName = printer.name;

                var option = document.createElement('option');
                option.value = printerName;
                option.appendChild(document.createTextNode(printerName));
                printersSelect.appendChild(option);
            }
        }
        */

        if (autoPrint) label.print(printer);

        return this;
    }

    $(document).on('click', '#dt-dymo-print', function() {
        label.print(printer);
    });

}( jQuery ));
