<?php

interface SocioTagDAO{
    public function create():bool;
    public function getById(int $id);
    public function getAll():array;
    public function delete(int $id):bool;
}