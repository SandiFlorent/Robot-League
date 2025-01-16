<?php

namespace App\triggers;

use App\Enum\State;
use App\Entity\Team;

class ChampionshipStatesTriggers
{
    private string $oldState;
    private string $newState;
    private Team $blueTeam;
    private Team $greenTeam;
    private $championship;
    public function __construct($championship, $oldState, $newState)
    {
        $this->oldState = $oldState;
        $this->newState = $newState;
        $this->blueTeam = $championship->getBlueTeam();
        $this->greenTeam = $championship->getGreenTeam();
        $this->championship = $championship;
    }
    public function discardGoals(): void
    {
        $this->blueTeam->setNbGoals(0);
        $this->greenTeam->setNbGoals(0);
        $this->championship->setBlueGoal(0);
        $this->championship->setGreenGoal(0);
    }

    public function updateNbEnCounterByOne($difference): void
    {
        $this->blueTeam->updateNbEncounter($difference);
        $this->greenTeam->updateNbEncounter($difference);
    }

    public function championshipStateTriggers()
    {
        //[NewState][OldState] = function to execute
        // CANCELED and NOT_STARTED are grouped together
        //dd($this->oldState, $this->newState);
        $transitions = [
            State::CANCELED->value => [
                State::WIN_BLUE->value => fn() => $this->onCancelFromWinBlue(),
                State::WIN_GREEN->value => fn() => $this->onCancelFromWinGreen(),
                State::DRAW->value => fn() => $this->onCancelFromDraw(),
                State::FORFAIT_BLUE->value => fn() => $this->onCancelForfaitBlue(),
                State::FORFAIT_GREEN->value => fn() => $this->onCancelForfaitGreen(),
            ],
            State::NOT_STARTED->value => [
                State::WIN_BLUE->value => fn() => $this->onCancelFromWinBlue(),
                State::WIN_GREEN->value => fn() => $this->onCancelFromWinGreen(),
                State::DRAW->value => fn() => $this->onCancelFromDraw(),
                State::FORFAIT_BLUE->value => fn() => $this->onCancelForfaitBlue(),
                State::FORFAIT_GREEN->value => fn() => $this->onCancelForfaitGreen(),
            ],
            State::IN_PROGRESS->value => [
                State::CANCELED->value => fn() => $this->onProgressFromCanceled(),
                State::WIN_BLUE->value => fn() => $this->onProgressFromWinBlue(),
                State::WIN_GREEN->value => fn() => $this->onProgressFromWinGreen(),
                State::DRAW->value => fn() => $this->onProgressFromDraw(),
                State::FORFAIT_BLUE->value => fn() => $this->onCancelForfaitBlue(),
                State::FORFAIT_GREEN->value => fn() => $this->onCancelForfaitGreen(),
            ],
            State::WIN_BLUE->value => [
                State::WIN_GREEN->value => fn() => $this->onCancelFromWinGreen(),
                State::NOT_STARTED->value => fn() => $this->onProgressFromCanceled(),
                State::CANCELED->value => fn() => $this->onProgressFromCanceled(),
                State::DRAW->value => fn() => $this->onProgressFromDraw(),
                State::FORFAIT_BLUE->value => fn() => $this->onCancelForfaitBlue(),
                State::FORFAIT_GREEN->value => fn() => $this->onCancelForfaitGreen(),
            ],
            // The same as WIN_BLUE but with the teams reversed
            State::WIN_GREEN->value => [
                State::CANCELED->value => fn() => $this->onCancelFromWinGreen(),
                State::NOT_STARTED->value => fn() => $this->onProgressFromCanceled(),
                State::DRAW->value => fn() => $this->onProgressFromDraw(),
                State::FORFAIT_BLUE->value => fn() => $this->onCancelForfaitBlue(),
                State::FORFAIT_GREEN->value => fn() => $this->onCancelForfaitGreen(),
                State::WIN_BLUE->value => fn() => $this->onCancelFromWinBlue(),
            ],
            State::DRAW->value => [
                State::CANCELED->value => fn() => $this->onProgressFromCanceled(),
                State::NOT_STARTED->value => fn() => $this->onProgressFromCanceled(),
                State::WIN_BLUE->value => fn() => $this->onCancelFromWinBlue(),
                State::WIN_GREEN->value => fn() => $this->onCancelFromWinGreen(),
                State::FORFAIT_BLUE->value => fn() => $this->onCancelForfaitBlue(),
                State::FORFAIT_GREEN->value => fn() => $this->onCancelForfaitGreen(),
            ],
            State::FORFAIT_BLUE->value => [
                //No need for canceled or not started states here (their points is gonna be updated in the global)
                State::IN_PROGRESS->value => fn() => $this->onForfaitBlueFromInProgress(),
                State::WIN_BLUE->value => fn() => $this->onCancelFromWinBlue(),
                State::WIN_GREEN->value => fn() => $this->onCancelFromWinGreen(),
                State::DRAW->value => fn() => $this->onCancelFromDraw(),
                State::FORFAIT_GREEN->value => fn() => $this->onCancelForfaitGreen(),

            ],
            State::FORFAIT_GREEN->value => [
                State::IN_PROGRESS->value => fn() => $this->onForfaitGreenFromInProgress(),
                State::WIN_BLUE->value => fn() => $this->onCancelFromWinBlue(),
                State::WIN_GREEN->value => fn() => $this->onCancelFromWinGreen(),
                State::DRAW->value => fn() => $this->onCancelFromDraw(),
                State::FORFAIT_BLUE->value => fn() => $this->onCancelForfaitBlue(),
            ],
        ];
        $oldState = $this->oldState;
        $newState = $this->newState;

        //dd($newState, $oldState);

        // Execute the corresponding action if defined
        if (isset($transitions[$newState][$oldState])) {
            $transitions[$newState][$oldState]();
        }
        // Additional global actions for specific new states
        if (($newState === State::CANCELED->value || $newState === State::NOT_STARTED->value)) {
            if($oldState !== State::CANCELED->value && $oldState !== State::NOT_STARTED->value){
                $this->updateNbEnCounterByOne(-1);
            }
            $this-> discardGoals();
        }

        if ($newState === State::WIN_BLUE->value){
            $this->blueTeam->updateTotalPoints(3);
            $this->blueTeam->updateNbWins(1);
        }

        if ($newState === State::WIN_GREEN->value){
            $this->greenTeam->updateTotalPoints(3);
            $this->greenTeam->updateNbWins(1);
        }

        if ($newState === State::DRAW->value){
            $this->blueTeam->updateTotalPoints(1);
            $this->greenTeam->updateTotalPoints(1);
        }

        if ($newState === State::FORFAIT_BLUE->value){
            $this->blueTeam->updateTotalPoints(-1);
        }

        if ($newState === State::FORFAIT_GREEN->value){
            $this->greenTeam->updateTotalPoints(-1);
        }

        if ($newState === State::IN_PROGRESS->value){
            $this->updateNbEnCounterByOne(1);
        }
    }

    private function onForfaitBlueFromNotStarted()
    {
        $this->blueTeam->updateTotalPoints(-1);
    }

    private function onForfaitBlueFromInProgress()
    {
        $this->updateNbEnCounterByOne(-1);
    }

    private function onForfaitGreenFromInProgress()
    {
        $this->updateNbEnCounterByOne(-1); 
    }


    private function onCancelFromInProgress()
    {
        $this->updateNbEnCounterByOne(-1);
    }

    private function onCancelFromWinBlue()
    {
        $this->blueTeam->updateTotalPoints(-3);
        $this->blueTeam->updateNbWins(-1);
    }

    private function onProgressFromWinGreen()
    {
        $this->greenTeam->updateTotalPoints(-3);
        $this->greenTeam->updateNbWins(-1);
        $this->updateNbEnCounterByOne(-1);
    }

    private function onCancelFromWinGreen()
    {
        $this->greenTeam->updateTotalPoints(-3);
        $this->greenTeam->updateNbWins(-1);
    }

    private function onCancelFromDraw()
    {
        $this->blueTeam->updateTotalPoints(-1);
        $this->greenTeam->updateTotalPoints(-1);
    }

    private function onProgressFromDraw()
    {
        $this->blueTeam->updateTotalPoints(-1);
        $this->greenTeam->updateTotalPoints(-1);
    }
    

    private function onCancelForfaitBlue()
    {
        $this->blueTeam->updateTotalPoints(1);
    }

    private function onGreenWinFromForfaitBlue()
    {
        $this->greenTeam->updateTotalPoints(4);
    }

    private function onBlueWinFromForfaitBlue()
    {
        $this->blueTeam->updateTotalPoints(4);
    }

    private function onBlueWinFromForfaitGreen()
    {
        $this->blueTeam->updateTotalPoints(4);
    }

    private function onGreenWinFromForfaitGreen()
    {
        $this->greenTeam->updateTotalPoints(4);
    }

    private function onCancelForfaitGreen()
    {
        $this->greenTeam->updateTotalPoints(1);
    }

    private function onProgressFromCanceled()
    {
        $this->updateNbEnCounterByOne(1);
    }

    private function onProgressFromWinBlue()
    {
        $this->blueTeam->updateTotalPoints(-3);
        $this->blueTeam->updateNbWins(-1);
        $this->updateNbEnCounterByOne(-1);
    }
}
