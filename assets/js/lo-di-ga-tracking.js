var lo_di_ga_tracking = {};
lo_di_ga_tracking.env = "%%environment%%";
lo_di_ga_tracking.account_ids = JSON.parse('%%account_ids%%');
lo_di_ga_tracking.namespace = "%%tracker_namespace%%";
lo_di_ga_tracking.form_types = JSON.parse('%%form_types%%');
lo_di_ga_tracking.form_mapping = JSON.parse('%%form_mapping%%');
lo_di_ga_tracking.allowed_modules = JSON.parse('%%allowed_modules%%');
lo_di_ga_tracking.version = '%%version%%';

window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
ga(function(){
    for (var i=0; i<lo_di_ga_tracking.account_ids.length; i++) {
        ga('create', lo_di_ga_tracking.account_ids[i], 'auto', lo_di_ga_tracking.namespace+'_'+i);
    }
});