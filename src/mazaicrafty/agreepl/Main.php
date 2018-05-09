<?php

namespace mazaicrafty\agreepl;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\utils\Utils;
use pocketmine\utils\Config;

use jojoe77777\FormAPI\FormAPI;

class Main extends PluginBase implements Listener{

    public function onEnable(): void{
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
        self::loadAPI();

        if (!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }
        $this->player_data = new Config($this->getDataFolder() . "data.yml", Config::YAML);
        $this->messages = new Config($this->getDataFolder() . "Messages.yml", Config::YAML, [
            "REGISTER-TITLE" => "sample1",
            "REGISTER-COTENT" => "sample2",
            "REGISTER-AGREE-BUTTON" => "button1",
            "REGISTER-DISAGREE-BUTTON" => "button2",
            "REGISTER-AGREE-MESSAGE" => "sample",
            "REGISTER-NORESULT-MESSAGE" => "sample",
            "REGISTER-DISAGREE-MESSAGE" => "sample",
            "REGISTERED-TITLE" => "sample1",
            "REGISTERED-CONTENT" => "sample2",
            "REGISTERED-AGREE-BUTTON" => "button1",
            "REGISTERED-AGREE-MESSAGE" => "sample"
        ]);
    }

    private $formapi;

    public function loadAPI(): void{
        $this->formapi = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
    }

    public function onJoin(PlayerJoinEvent $event): void{
        $bool = $this->player_data->exists($event->getPlayer()->getName());
        $method = ("create" . (!$bool ? "Register" : "Registered"));
        $this->$method($event->getPlayer());
    }

    private function createRegister(Player $player){
        $form = self::getFormAPI()->createSimpleForm(
            function (Player $player, $result){
                if ($result === null){
                    $message = $this->getMessage("REGISTER-NORESULT-MESSAGE");
                    $player->kick($message, false);
                    return;
                }
                switch ($result){
                    case self::AGREE:
                    $this->player_data->set($player->getName());
                    $message = $this->getMessage("REGISTER-AGREE-MESSAGE");
                    $player->sendMessage($message);
                    return;

                    case self::DISAGREE:
                    $message = $this->getMessage("REGISTER-DISAGREE-MESSAGE");
                    $player->kick($message, false);
                    return;
                }
            }
        );

        $form->setTitle($this->getMessage("REGISTER-TITLE"));
        $form->setContent($this->getMessage("REGISTER-CONTENT"));
        $form->addButton($this->getMessage("REGISTER-AGREE-BUTTON"));
        $form->addButton($this->getMessage("REGISTER-DISAGREE-BUTTON"));

        $form->sendToPlayer($player);
    }

    private function createRegistered(Player $player){
        $form = self::getFormAPI()->createSimpleForm(
            function (Player $player, $result){
                if ($result === null) return;
                switch ($result){
                    case self::AGREE:
                    $message = $this->getMessage("REGISTERED-AGREE-MESSAGE");
                    $player->sendMessage($message);
                    return;
                }
            }
        );

        $form->setTitle($this->getMessage("REGISTERED-TITLE"));
        $form->setContent($this->getMessage("REGISTERED-CONTENT"));
        $form->addButton($this->getMessage("REGISTERED-AGREE-BUTTON"));

        $form->sendToPlayer($player);
    }

    public function getMessage(string $message){
        $result = $this->messages->get($message);
        return $result;
    }

    private function getFormAPI(): FormAPI{
        return $this->formapi;
    }

    const AGREE = 0;
    const DISAGREE = 1;
}
