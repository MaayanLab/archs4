/**
 * Created by maayanlab on 9/16/16.
 */
$(document).ready(function(){

    $(function(){
        $('#ideal_form').submit(function(e){
            e.preventDefault();
            var form = $(this);
            var post_url = form.attr('api/files');
            var post_data = form.serialize();
            $('#loader3', form).html('<img src="/images/loading.gif" /> ');
            $.ajax({
                type: 'GET',
                url: post_url,
                data: post_data,
                success: function(msg) {
                    $(form).fadeOut(800, function(){
                        form.html(msg).fadeIn().delay(2000);

                    });
                }
            });
        });
    });

    $('#fileList').html("Updated file list");

    Dropzone.autoDiscover = false;

    Dropzone.options.drop = {
        url: 'upload.php',
        method: 'post',
        paramName: 'file',
        parallelUploads: 1,
        dictDefaultMessage: "Drag SRA files here for upload. Only SRA files supported at this point.",
        clickable: true,
        enqueueForUpload: true,
        maxFilesize: 6000,
        uploadMultiple: false,
        addRemoveLinks: false,
        acceptedFiles: ".sra,.SRA,.pdf,.PNG,.fa",

        init: function() {
            this.on("addedfile", function(file) {
                this.emit("thumbnail", file, "images/dna.png");
            }),

            this.on("complete", function(file, response) {
                this.removeFile(file);

                $.get("listfiles.php", function(data, status){
                    var res = data.split("<br>");
                    //alert("Data: " + res + "\nStatus: " + status);

                    var listelements = document.getElementsByClassName("list");
                    var listtext = [];
                    for(var i = 0; i<listelements.length; i++ ){
                        listtext.push(listelements[i].innerText);
                    }

                    A = listtext;
                    B = res;

                    var diff = $(B).not(A);

                    for(var i = 0; i<diff.length-1; i++ ){
                        var li = "<li class=\"list\">"+diff[i]+"</li>";
                        $("#available_list").append(li);
                    }

                    $.get("uploadlog.php?fileid="+file.name, function(data, status){});

                });
            })
        }
    }

    $('#drop').dropzone();

});
