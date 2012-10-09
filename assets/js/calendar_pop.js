$(function() {
    var dates = $( "#from, #to" ).datepicker({
        defaultDate: "+1w",
        dateFormat: 'mm-dd-yy',
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 1,
        onSelect: function( selectedDate ) {
            var option = this.id == "from" ? "minDate" : "maxDate",
                instance = $( this ).data( "datepicker" ),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings );
            dates.not( this ).datepicker( "option", option, date );
        },
        onClose: function( dateText, inst ) {
            var offset = (this.id == "from") ? 4 : 3;
            if((typeof filter_text == 'undefined') || !filterText) {
                offset = offset - 1;
            }
            $('a.customdate').each(function() {
                $(this).attr("href", function(index, old) {
                    var substr = old.split('/');
                    substr[substr.length - offset] = dateText;
                    return substr.join('/');
                });
            });
        },
        onChangeMonthYear: function( year, month, inst) {
            var offset = (this.id == "from") ? 4 : 3;
            if((typeof filter_text == 'undefined') || !filterText) {
                offset = offset - 1;
            }
            $('a.customdate').each(function() {
                $(this).attr("href", function(index, old) {
                    var substr = old.split('/');
                    var newDateText = new Array(3);
                    newDateText[0] = month;
                    newDateText[1] = '01';
                    newDateText[2] = year;
                    newDateText = newDateText.join('-');
                    substr[substr.length - offset] = newDateText;
                    return substr.join('/');
                });
            });
            $(this).datepicker('setDate', month + '-1-' + year);
        }
    });
});
