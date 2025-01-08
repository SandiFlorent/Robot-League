DELIMITER $$

CREATE TRIGGER before_update_championship
BEFORE UPDATE ON Championship
FOR EACH ROW

BEGIN

    -- Victory
    
    --blue team
    IF NEW.state = 'Victoire Blue' AND OLD.state =! 'Victoire Blue' AND OLD.state != 'Égalité' THEN
        UPDATE team
        SET total_points = total_points + 3, nb_win = nb_win + 1
        WHERE id = NEW.blue_team_id;
    END IF;

    IF NEW.state = 'Victoire Blue' AND OLD.state == "Égalité" THEN
        UPDATE team
        SET total_points = total_goal + 2
        WHERE id = NEW.blue_team_id;

    --green team

    -- égalité
    --blue team
    IF NEW.state = 'Égalité' AND (OLD.state == 'Victoire Blue' OR OLD.state == 'Victoire Green')THEN
        UPDATE team
        SET total_points = total_points - 2
        WHERE id = NEW.blue_team_id; 
    END IF;


END$$

DELIMITER ;
