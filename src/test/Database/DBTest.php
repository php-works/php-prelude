<?php

namespace Prelude\Database;

require_once __DIR__ . '/../../../include.php';

use Exception;
use PHPUnit_Framework_TestCase;


class DBTest extends PHPUnit_Framework_TestCase {
    function testRun() {
        // The registry pattern is not really the coolest :-(
        DBManager::registerDB('shop', ['dsn' => 'sqlite::memory:']);
       // DBManager::registerDB('shop', ['dsn' => 'mysql:host=localhost;dbname=test', 'username' => 'root']);
        
        $newUsers = [[
            'id' => 1001,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'city' => 'Seattle',
            'country' => 'USA',
            'type' => 1
        ], [
            'id' => 1002,
            'firstName' => 'Jimmy',
            'lastName' => 'Gym',
            'city' => 'Boston',
            'country' => 'USA',
            'type' => 1
        ], [
            
            'id' => 1003,
            'firstName' => 'Johnny',
            'lastName' => 'Chopper',
            'city' => 'Portland',
            'country' => 'USA',
            'type' => 2,
        ], [
            'id' => 1004,
            'firstName' => 'Jane',
            'lastName' => 'Whatever',
            'city' => 'London',
            'country' => 'UK',
            'type' => 2
        ]]; 

        $shopDB = DBManager::getDB('shop');

        $shopDB
            ->query('drop table if exists user')
            ->execute();

        $shopDB
            ->query('
                create table user
                (id integer primary key, firstName varchar(20), lastName varchar(20), city varchar(20), country varchar(20), type integer)
            ')
            ->execute();
       
       $shopDB->runTransaction(function () use ($shopDB, $newUsers) {
            $shopDB
                ->query('delete from user')
                ->execute();
                
            /* 
            $userCount = $shopDB
                ->query('
                    insert  into user values
                    (:id, :firstName, :lastName, :city, :country, :type)
                ')
                ->bindMany($newUsers)
                ->execute();
            */
            
            $userCount = count($newUsers);
            
            foreach ($newUsers as $user) {
                $shopDB
                    ->insertInto('user')
                    ->values($user)
                    ->execute();
            }
            
            $shopDB
                ->update('user')
                ->set(['lastName' => 'newLastName'])
                ->where('firstName=?', 'Jimmy')
                ->execute();
            
            
            $shopDB
                ->deleteFrom('user')
                ->where('lastName=?', 'newLastName')
                ->execute();
        });
        
        $users =
            $shopDB
                ->query('
                    select
                        *
                    from
                        user
                    where
                        country=:country and type=:type
                ')
                ->limit(100)
                ->bind(['country' => 'USA', 'type' => 2])
                ->fetchSeqOfDynObjects();    

        print "\nKnown users by ID:\n\n";
            
        foreach ($users as $user) {
            printf(
                "%d: %s %s (%s) - %s, %s\n",
                $user->id,
                $user->firstName,
                $user->lastName,
                $user->type,
                $user->city,
                $user->country
            );
        }
        
        $recs = 
            $shopDB
                ->from('user')
                ->select('firstName, lastName')
                ->where('country = ?', 'UK')
                ->orderBy('lastName')
                ->limit(100)
                ->fetchRecs();
                
       // print_r($recs);
    }
}
