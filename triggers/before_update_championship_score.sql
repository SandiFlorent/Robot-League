DELIMITER $$

CREATE TRIGGER before_update_championship_points
BEFORE UPDATE ON Championship
FOR EACH ROW
BEGIN
    -- Victory
    -- Blue team
    IF NEW.state = 'Victoire Blue' AND OLD.state <> 'Victoire Blue' AND OLD.state <> 'Égalité' THEN
        UPDATE team
        SET total_points = total_points + 3, nb_win = nb_win + 1
        WHERE id = NEW.blue_team_id;
    END IF;

    IF NEW.state = 'Victoire Blue' AND OLD.state = 'Égalité' THEN
        UPDATE team
        SET total_points = total_points + 2
        WHERE id = NEW.blue_team_id;
    END IF;

    -- Green team
    IF NEW.state = 'Victoire Green' AND OLD.state <> 'Victoire Green' AND OLD.state <> 'Égalité' THEN
        UPDATE team
        SET total_points = total_points + 3, nb_win = nb_win + 1
        WHERE id = NEW.green_team_id;
    END IF;

    IF NEW.state = 'Victoire Green' AND OLD.state = 'Égalité' THEN
        UPDATE team
        SET total_points = total_points + 2
        WHERE id = NEW.green_team_id;
    END IF;

    -- Draw (Égalité)
    -- Blue team
    IF NEW.state = 'Égalité' AND OLD.state = 'Victoire Blue' THEN
        UPDATE team
        SET total_points = total_points - 2
        WHERE id = NEW.blue_team_id; 
    END IF;

    -- Green team
    IF NEW.state = 'Égalité' AND OLD.state = 'Victoire Green' THEN
        UPDATE team
        SET total_points = total_points - 2
        WHERE id = NEW.green_team_id; 
    END IF;

    -- Both teams
    IF NEW.state = 'Égalité' AND OLD.state NOT IN ('Victoire Blue', 'Victoire Green', 'Égalité') THEN
        UPDATE team
        SET total_points = total_points + 1
        WHERE id IN (NEW.blue_team_id, NEW.green_team_id);
    END IF;
END$$

DELIMITER ;