/*
 * Description: FileUploader Class
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 21/04/2016
 * This code may not be reused without proper permission from its creator.
 */


function FileUploader(url, input, dropArea){
    if (typeof input == 'string') input = $(input);
    if (typeof dropArea == 'string') dropArea = $(dropArea);

    this.input = input;
    this.dropArea = dropArea;
    this.url = url;
    this.uploads = [];


    var that = this;

    this.input.on('change', function(){
        that.acquireFiles(this.files);
    });


    this.dropArea.on("dragleave", function (e) {
        var target = e.target;

        if (target && target === that.dropArea[0]) {
            $(this).removeClass("over");
        }

        e.preventDefault();
        e.stopPropagation();
    });

    this.dropArea.on("dragenter", function (e) {
        $(this).addClass("over");
        e.preventDefault();
        e.stopPropagation();
    });

    this.dropArea.on("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    this.dropArea.on("drop", function (e) {
        that.acquireFiles(e.originalEvent.dataTransfer.files);
        $(this).removeClass("over");
        e.preventDefault();
        e.stopPropagation();
    });
}
FileUploader.inherit(Emitter);
FileUploader.prototype.acquireFiles = function(files){
    if (typeof files !== "undefined") {
        for (var i=0, l=files.length; i<l; i++) {
            this.UploadFile(files[i]);
        }
    } else {
        console.log("No support for the File API in this web browser");
    }
};
FileUploader.prototype.UploadFile = function(file){
    var xhr;

    xhr = new XMLHttpRequest();
    xhr.open("post", this.url, true);

    var fileUpload = new FileUploader.FileUpload(file, xhr);
    this.uploads.push(fileUpload);
    this.emit('uploadstart', [fileUpload]);

    var that = this;
    fileUpload.on('complete', function(){
        that.emit('uploadfinish', [fileUpload]);
    });

    fileUpload.Upload();
};

FileUploader.FileUpload = function (file, xhr){
    this.file = file;
    this.xhr = xhr;
    this.progress = 0;
    this.completed = false;
    this.imageData = null;

    //this.xhr.setRequestHeader("Content-Type", "multipart/form-data");
    //this.xhr.setRequestHeader("X-File-Name", this.file.name);
    //this.xhr.setRequestHeader("X-File-Size", this.file.size);
    //this.xhr.setRequestHeader("X-File-Type", this.file.type);

    var that = this;
    this.xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
            that.progress = (evt.loaded / evt.total);
            that.emit('progress');
        }
    }, false);
    this.xhr.addEventListener("load", function () {
        that.completed = true;
        that.response = that.xhr.response;

        that.emit('complete');
    }, false);
};
FileUploader.FileUpload.inherit(Emitter);
FileUploader.FileUpload.prototype.IsImage = function (){
    return (/image/i).test(this.file.type);
};
FileUploader.FileUpload.prototype.FetchImageData = function (){
    if (typeof FileReader !== "undefined" && this.IsImage()) {
        var reader = new FileReader();

        var that = this;
        reader.onload = function (evt) {
            that.imageData = evt.target.result;
            that.emit('imageload')
        };
        reader.readAsDataURL(this.file);
    }
};
FileUploader.FileUpload.prototype.Upload = function (){
    var fd = new FormData();
    fd.append("file", this.file);

    this.xhr.send(fd);
};