<?php
interface CaptchaService{
    public function getApi():string;
    public function getWidget():string;
    public function validate():bool;
}