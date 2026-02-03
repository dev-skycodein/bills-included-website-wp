"use strict";
var action = document.querySelectorAll(".action_dropdown ul");
var id= 1;
action.forEach(function(el){
  el.id = 'ul'+id;
  id++;
});


for (const dropdown of document.querySelectorAll(".action_dropdown")) {
  dropdown.addEventListener('click', function (event) {
    var thisId = this.querySelector('ul').id;
    listinghub_closeAll(thisId);
    this.querySelector('ul').classList.toggle('ul_open');
  });
}



function listinghub_closeAll(getId){
	"use strict";
  var actionUl = document.querySelectorAll(".action_dropdown ul");
  actionUl.forEach(function(el){
    if(getId === el.id){
      return;
    }
    else{
      el.classList.remove('ul_open');
    }
  });
}


document.addEventListener('click', function (e) {
    if(e.target.closest('.action_dropdown') ){
     
      return;
    }
    else{
     
      var container = document.querySelectorAll(".action_dropdown ul");
      container.forEach(function(el){
        if(el.classList.contains('ul_open')){
          el.classList.remove('ul_open');
        }
      });
    }
});
