//Password validator:

$.validator.addMethod('validPassword',
    function(value, element, param){
        if(value != ''){
            if(value.match(/.*[a-z]+.*/i) == null){
                return false;
            }
            if(value.match(/.*\d+.*/) == null){
                return false;
            }
        }

        return true;
    },
    'Has&lstrok;o musi zawiera&cacute; co najmniej jedn&aogon; liter&eogon; i jedn&aogon; cyfr&eogon;'
);

//Date selector modal:

function onChangeFun() {
    selected_val = document.getElementById('period').value;
  
    if (selected_val == "1" || selected_val == "2" || selected_val == "3") {
      document.forms['choice'].submit();
    } else if (selected_val == "4") {
      $(document).ready(function(){
        $('#myModal').modal("show");
      });
    }
  };