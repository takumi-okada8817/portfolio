'use strict';

var height = $('header').height();

//ログインしていない場合
function modalResize(){
    var height = window.innerHeight;
    var width = window.innerWidth;
    var contenth = $('#modal-content').outerHeight(true);
    var contentw = $('#modal-content').outerWidth(true);
    //var contenth = document.getElementById('modal-content').offsetHeight;
    //var contentw = document.getElementById('modal-content').offsetWidth;

    var centerheight = (height - contenth)/2;
    var centerwidth = (width - contentw)/2;

    $('#modal-content').css({'top':centerheight+'px','left':centerwidth+'px'});
}

function modal(){
    $('body').append('<div id="modal-content"><div><p id="modal-title">Find a small happiness</p><img src="images/HatchfulExport-All/pinterest_profile_image.png" alt="find a small happiness"><p id="link"><a href="index.php">ログイン</a></p></div></div><div id="modal-overlay"></div>');

    modalResize();

    $('#modal-content,#modal-overlay').fadeIn();

    $(window).on('resize',function(){
        modalResize();
    });
}

//ログアウトの確認
function modalresize(){
    var height = window.innerHeight;
    var width = window.innerWidth;
    var contenth = $('#logout-modal').outerHeight(true);
    var contentw = $('#logout-modal').outerWidth(true);

    var centerheight = (height - contenth)/2;
    var centerwidth = (width - contentw)/2;

    $('#logout-modal').css({'top':centerheight+'px','left':centerwidth+'px'});
}

function logout(){
    $('body').append('<div id="logout-modal" style="display:none;"><div><p id="modal-name">ログアウトしますか？</p></div><div id="option"><div class="options"><form action="#" method="POST" name="logoutform"><button type="submit" id="logoutbutton" name="logout">ログアウト</button></form></div><div class="options"><a href="#" id="modal-close">キャンセル</a></div></div></div><div id="logout-overlay"></div>');

    modalresize();

    $('#logout-modal,#logout-overlay').fadeIn();

    $(window).on('resize',function(){
        modalresize();
    });

    $('#modal-close,#logout-overlay').unbind().click(function(){
        $('#logout-modal,#logout-overlay').fadeOut(function(){
            $('#logout-modal,#logout-overlay').remove();
        });
    });
}

//投稿
function resize(){
    var height = window.innerHeight;
    var width = window.innerWidth;
    var contenth = $('.form').outerHeight(true);
    var contentw = $('.form').outerWidth(true);

    var centerheight = (height - contenth)/2;
    var centerwidth = (width - contentw)/2;

    $('.form').css({'top':centerheight+'px','left':centerwidth+'px'});
}
    
function create(){
    $('.form, .form-overlay').fadeToggle(300);
    $('.form-overlay').css('margin-top',height-70);
    resize();
    var formheight = $('.form').height();
    $('.form form textarea').css({'height':formheight-20});

    $(window).on('resize',()=>{
        resize();
        var formheight = $('.form').height();
        $('.form form textarea').css({'height':formheight-20});
    });
    $('.form-overlay').unbind().on('click',()=>{
        $('.form,.form-overlay').fadeOut(300);
    });
}