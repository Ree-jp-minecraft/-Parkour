<?php


namespace Ree\parkour;



use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class ParkourCheckTask extends Task
{
    /**
     * @var Player
     */
    private $p;
    /**
     * @var main
     */
    private $main;
    public function __construct(Player $p ,main $server)
    {
        $this->p = $p;
        $this->main = $server;
    }
    public function onRun(int $currentTick)
    {
        // TODO: Implement onRun() method.
        $vec3 = $this->p->asVector3();
        $bl = $this->p->getLevel()->getBlock($vec3);
        if ($bl->getId() == Item::END_PORTAL_FRAME)
        {
            $level = $this->p->getLevel()->getName();
            if ($this->main->data->exists($level))
            {
                $arraydata = $this->main->data->get($level);
                foreach ($arraydata as $data)
                {
                    if ($data["x"] == $this->p->getFloorX() and $data["y"] == $this->p->getFloorY() and $data["z"] == $this->p->getFloorZ())
                    {
                        $this->main->checkPoint($this->p ,$data["id"] ,$data["point"] ,$data["goal"]);
                    }
                }
            }
        }
        if ($this->main->time[$this->p->getName()])
        {
            $time = $this->main->time[$this->p->getName()] + 0.1;
            $this->p->sendTip("ParkourTime : ".$time);
            $this->main->time[$this->p->getName()] = $time;
        }
    }
}