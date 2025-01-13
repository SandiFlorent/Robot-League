<?php

namespace App\triggers;

use App\Enum\State;
use App\Entity\Team;

class triggers
{
    private State $oldState;
    private State $newState;
    private Team $blueTeam;
    private Team $greenTeam;
    private int $blueGoal = 0;
    private int $greenGoal = 0;
    public function __construct($oldState, $newState, $blueTeam, $greenTeam, $blueGoal, $greenGoal)
    {
        $this->oldState = $oldState;
        $this->newState = $newState;
        $this->blueTeam = $blueTeam;
        $this->greenTeam = $greenTeam;
        $this->blueGoal = $blueGoal;
        $this->greenGoal = $greenGoal;
    }

    public function discardGoals(): void
    {
        //We reduce the total goals of the blue and green team
        $this->blueTeam->updateTotalGoals(-$this->blueGoal);
        $this->greenTeam->updateTotalGoals(-$this->greenGoal);

        //We set back this championship's goals to 0
        $this->blueGoal = 0;
        $this->greenGoal = 0;
    }

    public function updateNbEnCounterByOne($difference): void
    {
        $this->blueTeam->updateNbEncounter($difference);
        $this->greenTeam->updateNbEncounter($difference);
    }

    public function championshipStateTriggers()
    {
        $oldState = $this->oldState;
        $newState = $this->newState;
        switch ($newState) {
                // If a match is canceled
            case State::CANCELED->value:
                switch ($oldState) {

                        // If before that it was in progress, then
                    case State::IN_PROGRESS->value:
                        //We reduce played match by one
                        $this->updateNbEnCounterByOne(-1);

                        // If before it was a blue win then:
                    case State::WIN_BLUE->value:
                        //We reduce the blue team's total points by 3 (score change should be triggered) and reduce the nbWins
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->blueTeam->updateNbWins(-1);

                        //We reduce the nbEncounter by one
                        $this->updateNbEnCounterByOne(-1);
                        break;


                    case State::WIN_GREEN->value:
                        //We reduce the green team's total points by 3 (score change should be triggered) and reduce the nbWins
                        $this->greenTeam->updateTotalPoints(-1);
                        $this->greenTeam->updateNbWins(-1);

                        //We reduce the nbEncounter by one
                        $this->updateNbEnCounterByOne(-1);
                        break;

                    case State::DRAW->value:
                        //We reduce both teams total points by 1 (score change should be triggered) and reduce the nbEncounter
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->greenTeam->updateTotalPoints(-1);
                        $this->updateNbEnCounterByOne(-1);

                        break;
                    case State::FORFAIT_BLUE->value:
                        //We reduce the blue team's total points by 1 (score change should be triggered)
                        $this->blueTeam->updateTotalPoints(-1);

                        break;
                    case State::FORFAIT_GREEN->value:
                        //We reduce the green team's total points by 1 (score change should be triggered)
                        $this->greenTeam->updateTotalPoints(-1);
                        break;

                        // If the match was not started, then canceling it does not affect the teams
                    default:
                        break;
                }
                // We discard the goals if the match is canceled
                $this->discardGoals();
                break;

                // If the match is in progress
            case State::IN_PROGRESS->value:
                switch ($oldState) {
                        //If the match canceled before, we increase the number of encounters for both teams
                    case State::CANCELED->value:
                        $this->updateNbEnCounterByOne(1);
                        break;

                        // If it was in progress before, then we do nothing
                    case State::IN_PROGRESS->value:
                        break;

                        // If the blue team won before, we reduce the total points of the blue team by 3 and reduce the number of wins
                    case State::WIN_BLUE->value:
                        $this->blueTeam->updateTotalPoints(-3);
                        $this->blueTeam->updateNbWins(-1);
                        break;

                        // If the green team won before, we reduce the total points of the green team by 3 and reduce the number of wins
                    case State::WIN_GREEN->value:
                        $this->greenTeam->updateTotalPoints(-3);
                        $this->greenTeam->updateNbWins(-1);
                        break;

                        // If it was a draw before, we reduce the total points of both teams by 1
                    case State::DRAW->value:
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->greenTeam->updateTotalPoints(-1);
                        break;

                        // If the blue team forfeited before, we augment the total points of the blue team by 1
                    case State::FORFAIT_BLUE->value:
                        $this->blueTeam->updateTotalPoints(1);
                        break;

                        // If the green team forfeited before, we augment the total points of the green team by 1
                    case State::FORFAIT_GREEN->value:
                        $this->greenTeam->updateTotalPoints(1);
                        break;

                        // If the match was not started
                    default:
                        //We increase the number of encounters for both teams
                        $this->updateNbEnCounterByOne(1);
                        break;
                }

            case State::NOT_STARTED->value:
                switch ($oldState) {
                        // If the match was canceled before, then we do nothing
                    case State::CANCELED->value:
                        break;

                        // If the match was in progress before, then we reduce the number of encounters for both teams
                    case State::IN_PROGRESS->value:
                        $this->blueTeam->updateNbEncounter(-1);
                        $this->greenTeam->updateNbEncounter(-1);
                        break;

                        // If the blue team won before, we reduce the total points of the blue team by 3 and reduce the number of wins
                    case State::WIN_BLUE->value:
                        $this->blueTeam->updateTotalPoints(-3);
                        $this->blueTeam->updateNbWins(-1);
                        break;

                        // If the green team won before, we reduce the total points of the green team by 3 and reduce the number of wins
                    case State::WIN_GREEN->value:
                        $this->greenTeam->updateTotalPoints(-3);
                        $this->greenTeam->updateNbWins(-1);
                        break;

                        // If it was a draw before, we reduce the total points of both teams by 1 
                    case State::DRAW->value:
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->greenTeam->updateTotalPoints(-1);
                        break;

                        // If the blue team forfeited before, we augment the total points of the blue team by 1
                    case State::FORFAIT_BLUE->value:
                        $this->blueTeam->updateTotalPoints(1);
                        break;

                        // If the green team forfeited before, we augment the total points of the green team by 1
                    case State::FORFAIT_GREEN->value:
                        $this->greenTeam->updateTotalPoints(1);
                        break;

                        // If we switch to not started, we do nothing
                    default:
                        break;
                }
                // If we switch to not started, we discard the goals
                $this->discardGoals();
                break;

            case State::WIN_BLUE->value:
                switch ($oldState) {
                        // If the match was canceled before, we increase the number of encounters for both teams and add 3 points to the blue team and increase its number of wins
                    case State::CANCELED->value:
                        $this->updateNbEnCounterByOne(1);
                        $this->blueTeam->updateTotalPoints(3);
                        $this->blueTeam->updateNbWins(1);
                        break;

                        // If the match was in progress before, we add 3 points to the blue team and increase the number of wins
                    case State::IN_PROGRESS->value:
                        $this->blueTeam->updateTotalPoints(3);
                        $this->blueTeam->updateNbWins(1);
                        break;

                        // If it has'nt started before, we increase the number of encounters for both teams and add 3 points to the blue team and increase its number of wins
                    case State::NOT_STARTED->value:
                        $this->updateNbEnCounterByOne(1);
                        $this->blueTeam->updateTotalPoints(3);
                        $this->blueTeam->updateNbWins(1);
                        break;

                        // If the green team won before, we reduce the total points of the green team by 3 and reduce its number of wins. We increase the blue team's total points by 3 and increase its number of wins
                    case State::WIN_GREEN->value:
                        $this->greenTeam->updateTotalPoints(-3);
                        $this->greenTeam->updateNbWins(-1);

                        $this->blueTeam->updateTotalPoints(3);
                        $this->blueTeam->updateNbWins(1);
                        break;

                        // If it was a draw before, we reduces green teams total points by 1, increase blue teams total points by 2 and add 1 to its number of wins
                    case State::DRAW->value:
                        $this->greenTeam->updateTotalPoints(-1);
                        $this->blueTeam->updateTotalPoints(2);
                        $this->blueTeam->updateNbWins(1);
                        break;

                        // If the blue team forfeited before, we augment the total points of the blue team by 4 and increase its number of wins
                    case State::FORFAIT_BLUE->value:
                        $this->blueTeam->updateTotalPoints(4);
                        $this->blueTeam->updateNbWins(1);
                        break;

                        // If the green team forfeited before, we augment the total points of the green team by 1 and increase blue teams total points by 3 and add 1 to its number of wins
                    case State::FORFAIT_GREEN->value:
                        $this->greenTeam->updateTotalPoints(1);
                        $this->blueTeam->updateTotalPoints(3);
                        $this->blueTeam->updateNbWins(1);
                        break;

                        // If we switch to not started, we do nothing
                    default:
                        break;
                }
                break;

                // If the green team wins it's like the blue team wins but with the teams switched
            case State::WIN_GREEN->value:
                switch ($oldState) {
                        // If the match was canceled before, we increase the number of encounters for both teams and add 3 points to the green team and increase its number of wins
                    case State::CANCELED->value:
                        $this->updateNbEnCounterByOne(1);
                        $this->greenTeam->updateTotalPoints(3);
                        $this->greenTeam->updateNbWins(1);
                        break;

                        // If the match was in progress before, we add 3 points to the green team and increase the number of wins
                    case State::IN_PROGRESS->value:
                        $this->greenTeam->updateTotalPoints(3);
                        $this->greenTeam->updateNbWins(1);
                        break;

                        // If it hasn't started before, we increase the number of encounters for both teams and add 3 points to the green team and increase its number of wins
                    case State::NOT_STARTED->value:
                        $this->updateNbEnCounterByOne(1);
                        $this->greenTeam->updateTotalPoints(3);
                        $this->greenTeam->updateNbWins(1);
                        break;

                        // If the blue team won before, we reduce the total points of the blue team by 3 and reduce its number of wins. We increase the green team's total points by 3 and increase its number of wins
                    case State::WIN_BLUE->value:
                        $this->blueTeam->updateTotalPoints(-3);
                        $this->blueTeam->updateNbWins(-1);

                        $this->greenTeam->updateTotalPoints(3);
                        $this->greenTeam->updateNbWins(1);
                        break;

                        // If it was a draw before, we reduces blue teams total points by 1, increase green teams total points by 2 and add 1 to its number of wins
                    case State::DRAW->value:
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->greenTeam->updateTotalPoints(2);
                        $this->greenTeam->updateNbWins(1);
                        break;

                        // If the blue team forfeited before, we augment the total points of the blue team by 1 and increase green teams total points by 3 and add 1 to its number of wins
                    case State::FORFAIT_BLUE->value:
                        $this->blueTeam->updateTotalPoints(1);
                        $this->greenTeam->updateTotalPoints(3);
                        $this->greenTeam->updateNbWins(1);
                        break;

                        // If the green team forfeited before, we augment the total points of the green team by 4 and increase its number of wins
                    case State::FORFAIT_GREEN->value:
                        $this->greenTeam->updateTotalPoints(4);
                        $this->greenTeam->updateNbWins(1);
                        break;

                        // If we switch to this very state, we do nothing
                    default:
                        break;
                }
                break;

            case State::DRAW->value:
                switch ($oldState) {
                        // If the match was canceled or didn't started before, we increase the number of encounters for both teams and add 1 point to both teams
                    case State::CANCELED->value:
                    case State::NOT_STARTED->value:
                        $this->updateNbEnCounterByOne(1);
                        $this->blueTeam->updateTotalPoints(1);
                        $this->greenTeam->updateTotalPoints(1);
                        break;

                        // If the match was in progress before, we add 1 point to both teams
                    case State::IN_PROGRESS->value:
                        $this->blueTeam->updateTotalPoints(1);
                        $this->greenTeam->updateTotalPoints(1);
                        break;

                        // If the blue team won before, we reduce the total points of the blue team by 2 and reduce its number of wins. We increase the green team's total points by 1
                    case State::WIN_BLUE->value:
                        $this->blueTeam->updateTotalPoints(-2);
                        $this->blueTeam->updateNbWins(-1);
                        $this->greenTeam->updateTotalPoints(1);
                        break;

                        // If the green team won before, we reduce the total points of the green team by 2 and reduce its number of wins. We increase the blue team's total points by 1
                    case State::WIN_GREEN->value:
                        $this->greenTeam->updateTotalPoints(-2);
                        $this->greenTeam->updateNbWins(-1);
                        $this->blueTeam->updateTotalPoints(1);
                        break;

                        // If the blue team forfeited before, we augment the total points of the blue team by 2 and increase the total points of the green team by 1
                    case State::FORFAIT_BLUE->value:
                        $this->blueTeam->updateTotalPoints(2);
                        $this->greenTeam->updateTotalPoints(1);
                        break;

                        // If the green team forfeited before, we augment the total points of the green team by 2 and increase the total points of the blue team by 1
                    case State::FORFAIT_GREEN->value:
                        $this->greenTeam->updateTotalPoints(2);
                        $this->blueTeam->updateTotalPoints(1);
                        break;

                    default:
                        break;
                }
                break;

            case State::FORFAIT_GREEN->value:
                switch ($oldState) {
                        // If the match was canceled or didn't started before, we we decrease the grean team's score by 1.
                    case State::CANCELED->value:
                    case State::NOT_STARTED->value:
                        $this->greenTeam->updateTotalPoints(-1);
                        break;

                        // If the match was in progress, we decrease the green team's score by 1 and decrease the number of encounters
                    case State::IN_PROGRESS->value:
                        $this->greenTeam->updateTotalPoints(-1);
                        $this->updateNbEnCounterByOne(-1);
                        break;

                        // If the blue team won before, we reduce the total points of the blue team by 3 and reduce its number of wins. We increase the green team's total points by 1 and decrease the number of encounters
                    case State::WIN_BLUE->value:
                        $this->blueTeam->updateTotalPoints(-3);
                        $this->blueTeam->updateNbWins(-1);
                        $this->greenTeam->updateTotalPoints(1);
                        $this->updateNbEnCounterByOne(-1);
                        break;


                        // If the green team won before, we reduce the total points of the green team by 4 and reduce its number of wins and reduce the number of encounters
                    case State::WIN_GREEN->value:
                        $this->greenTeam->updateTotalPoints(-4);
                        $this->greenTeam->updateNbWins(-1);
                        $this->updateNbEnCounterByOne(-1);
                        break;

                        // If it was a draw before, we reduce the green team's total points by 4 and the blue team's by 1 and decrease the number of encounters
                    case State::DRAW->value:
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->greenTeam->updateTotalPoints(-4);
                        $this->updateNbEnCounterByOne(-1);
                        break;

                        // If the blue team forfeited before, we increase the total points of the blue team by 1 and decrease the green team's total points by 1
                    case State::FORFAIT_BLUE->value:
                        $this->blueTeam->updateTotalPoints(1);
                        $this->greenTeam->updateTotalPoints(-1);
                        break;

                        // If the green team forfeited before, we do nothing
                    default:
                        break;
                }
                // If there is a forfeit, we discard the goals
                $this->discardGoals();
                break;

                // The same as the green team forfeit but with the teams switched
            case State::FORFAIT_BLUE->value:
                switch ($oldState) {
                        // If the match was canceled or didn't started before, we we decrease the blue team's score by 1.
                    case State::CANCELED->value:
                    case State::NOT_STARTED->value:
                        $this->blueTeam->updateTotalPoints(-1);
                        break;

                        // If the match was in progress, we decrease the blue team's score by 1 and decrease the number of encounters
                    case State::IN_PROGRESS->value:
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->updateNbEnCounterByOne(-1);
                        break;

                        // If the blue team won before, we reduce the total points of the blue team by 4 and reduce its number of wins and reduce the number of encounters
                    case State::WIN_BLUE->value:
                        $this->blueTeam->updateTotalPoints(-4);
                        $this->blueTeam->updateNbWins(-1);
                        $this->updateNbEnCounterByOne(-1);
                        break;

                        // If the green team won before, we reduce the total points of the green team by 3 and reduce its number of wins. We decrease the blue team's total points by 1 and decrease the number of encounters
                    case State::WIN_GREEN->value:
                        $this->greenTeam->updateTotalPoints(-3);
                        $this->greenTeam->updateNbWins(-1);
                        $this->blueTeam->updateTotalPoints(-1);
                        $this->updateNbEnCounterByOne(-1);
                        break;

                        // If it was a draw before, we reduce the blue team's total points by 2 and the green team's by 1. We decrease the number of encounters
                    case State::DRAW->value:
                        $this->greenTeam->updateTotalPoints(-1);
                        $this->blueTeam->updateTotalPoints(-2);
                        $this->updateNbEnCounterByOne(-1);
                        break;

                    case State::FORFAIT_GREEN->value:
                        $this->greenTeam->updateTotalPoints(1);
                        $this->blueTeam->updateTotalPoints(-1);
                        break;

                    default:
                        break;
                }
            default:
                break;
        }
    }
}
