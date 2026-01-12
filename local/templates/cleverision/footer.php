<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$curPage = $APPLICATION->GetCurPage();
// todo: убрать условие /layout/ из подстановки класса indexVersion
?>
        </div>
        <div class="footer<?=($curPage === "/" || $curPage === "/layout/" ? " indexVersion" : "")?>">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="footerContent">
                            <div class="row align-items-center">
                                <div class="item col-12 col-lg-auto d-flex justify-content-center">
                                    <a class="logo" href="/">
                                        <div class="icon"></div>
                                        <div class="text">CLEVERISION</div>
                                    </a>
                                </div>
                                <div class="item col-12 col-lg align-items-center">
                                    <div class="description">Данный интернет-сайт носит исключительно информационный характер и ни при каких условиях не является публичной офертой, определяемой положениями Статьи 437 (2) Гражданского кодекса Российской Федерации.</div>
                                </div>
                                <div class="item col-12 col-lg-auto d-flex justify-content-center">
                                    <span class="phoneText text-nowrap">Консультации и запись по телефонам:<br />8-903-831-43-39</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
