<?php

namespace Ree\parkour;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

class main extends PluginBase implements Listener
{
    /**
     * @var Config
     */
    public $data;
    /**
     * @var int[]
     */
    private $task;
    /**
     * @var float[]
     */
    public $time;
    /**
     * @var array[]
     */
    private $point;

    public function onEnable()
    {
        $this->getLogger()->info("loading now...");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML);
    }

    public function onJoin(PlayerJoinEvent $ev)
    {
        $p = $ev->getPlayer();
        $this->time[$p->getName()] = false;
        $this->task[$p->getName()] = $this->getScheduler()->scheduleRepeatingTask(new ParkourCheckTask($p, $this), 2)->getTaskId();
    }

    public function onQuit(PlayerQuitEvent $ev)
    {
        $p = $ev->getPlayer();
        $this->getScheduler()->cancelTask($this->task[$p->getName()]);
    }

    public function onBreak(BlockBreakEvent $ev)
    {
        $p = $ev->getPlayer();
        $bl = $ev->getBlock();
        if ($bl->getId() == Block::END_PORTAL_FRAME) {
            $level = $p->getLevel()->getName();
            if ($this->data->exists($level)) {
                $arraydata = $this->data->get($level);
                $i = 0;
                foreach ($arraydata as $data) {
                    if ($data["x"] == $bl->getFloorX() and $data["y"] == $bl->getFloorY() and $data["z"] == $bl->getFloorZ()) {
                        unset($arraydata[$i]);
                        $this->data->set($level, $arraydata);
                        $this->data->save();
                        $p->sendMessage("コース:" . $data["id"] . "ポイント:" . $data["point"] . "を破壊しました");
                    }
                    $i++;
                }
            }
        }
    }

    public
    function onPlace(BlockPlaceEvent $ev)
    {
        $p = $ev->getPlayer();
        if ($ev->getBlock()->getId() == Item::END_PORTAL_FRAME) {
            $p->sendForm(new ParkourForm($this, $ev->getBlock()));
        }
    }

    /**
     * @param array $data
     * @param Player $p
     */
    public
    function setPoint(array $data, Player $p, int $x, int $y, int $z): void
    {
        $level = $p->getLevel()->getName();
        $array["x"] = $x;
        $array["y"] = $y;
        $array["z"] = $z;
        $array["id"] = $data[0];
        $array["point"] = $data[1];
        $array["goal"] = $data[2];
        if ($this->data->exists($level)) {
            $arraydata = $this->data->get($level);
        }
        $arraydata[] = $array;
        $this->data->set($level, $arraydata);
        $this->data->save();
        $p->sendMessage("§a>>成功しました");
    }

    /**
     * @param Player $p
     * @param string $id
     * @param int $point
     * @param bool $bool
     */
    public
    function checkPoint(Player $p, string $id, int $point, bool $bool)
    {
        $n = $p->getName();
        if (!isset($this->point[$n])) {
            if ($point != 0) {
                return;
            }
            $this->time[$n] = 0.1;
            $array["id"] = $id;
            $array["point"] = $point;
            $this->point[$n] = $array;
            $p->sendMessage("§a>>§rパルクールスタート");
            if ($bool) {
                $p->sendMessage("§a>>§rパルクール終了\n§2コース : " . $id . "\n§6タイム : " . $this->time[$n]);
                unset($this->point[$n]);
                $this->time[$n] = false;
            }
        } else {
            $array = $this->point[$n];
            if ($array["id"] == $id) {
                $array["point"]++;
                if ($array["point"] == $point) {
                    if ($bool) {
                        $p->sendMessage("§a>>§rパルクール終了\n§2コース : " . $id . "\n§6タイム : " . $this->time[$n]);
                        unset($this->point[$n]);
                        $this->time[$n] = false;
                        return;
                    }
                    $p->sendMessage("§a>>§rチェックポイント" . $point . "を通過しました\n§6タイム : " . $this->time[$n]);
                    $this->point[$n] = $array;
                }
            }
        }

    }
}
