DELIMITER $$

CREATE TRIGGER before_update_championship
BEFORE UPDATE ON Championship
FOR EACH ROW
BEGIN
    -- Update blue team's total goals
    IF NEW.blue_goal != OLD.blue_goal THEN
        UPDATE team
        SET total_goal = total_goal + (NEW.blue_goal - OLD.blue_goal)
        WHERE id = NEW.blue_team_id;
    END IF;

    -- Update green team's total goals
    IF NEW.green_goal != OLD.green_goal THEN
        UPDATE team
        SET total_goal = total_goal + (NEW.green_goal - OLD.green_goal)
        WHERE id = NEW.green_team_id;
    END IF;
END$$

DELIMITER ;
