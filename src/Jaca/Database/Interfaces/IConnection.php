<?php
namespace Jaca\Database\Interfaces;

interface IConnection
{
    public static function getInstance(): IConnection;
    public function getConnection(): \PDO;
}