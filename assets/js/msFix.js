var bodyClasses = document.body.getAttribute('class');

if(!featureOK('width', 'fit-content')){
    bodyClasses += ' noFitContent';
}

if(!featureOK('height','initial')){
    bodyClasses += ' noHtInit';
}

if(!featureOK('object-fit','cover')){
    bodyClasses += ' noObjFit';    
}

if(!featureOK('text-transform','initial')){
    bodyClasses += ' noTxtTransInit';    
}

if(!featureOK('border','ititial')){
    bodyClasses += ' noBorderInit';    
}

if(!featureOK('text-indent','ititial')){
    bodyClasses += ' noTxtIndtInit';    
}

document.body.setAttribute('class',bodyClasses);

function featureOK(prop, val) {
    var testEl = document.createElement('div');
    testEl.setAttribute('id',prop+'Div');
    var prefixes = ['', '-moz-', '-webkit-', '-o-', '-ms-'];
    var supported = false;
    var succesfulRule;

    for (pref in prefixes) {
        if (supported === false) {
            testEl.style[prop] = prefixes[pref] + val;
            if (testEl.getAttribute('style')) {
                supported = true;
                succesfulRule = prop + ': ' + prefixes[pref] + val;
            } 
        }
    }    
    return supported;
}