<?php


namespace Ree\parkour;


use pocketmine\block\Block;
use pocketmine\form\Form;
use pocketmine\Player;

class ParkourForm implements Form
{
    /**
     * @var main
     */
    private $server;
    /**
     * @var Block
     */
    private $bl;
    public function __construct(main $server ,Block $bl)
    {
        $this->server = $server;
        $this->bl = $bl;
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return [
            'type' => 'custom_form',
            'title' => '§aReef§eNetwork',
            'content' => [
                [
                    "type" => "input",
                    "text" => "ParkourId",
                    "placeholder" => "int",
                    "default" => ""
                ],
                [
                    "type" => "input",
                    "text" => "Checkpoint",
                    "placeholder" => "int",
                    "default" => ""
                ],
                [
                    "type" => "toggle",
                    "text" => "Goal",
                    "default" => false,
                ],
            ]
        ];
    }

    public function handleResponse(Player $p, $data): void
    {
        // TODO: Implement handleResponse() method.
        if ($data === null) {
            return;
        }
        if (!ctype_digit(strval($data[0])))
        {
            $p->sendMessage("§cParkourIdが不正な値です");
            return;;
        }
        if (!ctype_digit(strval($data[1])))
        {
            $p->sendMessage("§cCheckpointが不正な値です");
            return;
        }
        $this->server->setPoint($data ,$p ,$this->bl->getFloorX() ,$this->bl->getFloorY() ,$this->bl->getFloorZ());
    }
}