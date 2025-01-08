DELIMITER $$

CREATE TRIGGER before_update_team_score
BEFORE UPDATE ON team
FOR EACH ROW
BEGIN
    IF NEW.total_points != OLD.total_points OR NEW.nb_encounter != OLD.nb_encounter THEN
        IF NEW.nb_encounter > 0 THEN
            SET NEW.score = NEW.total_points / NEW.nb_encounter;
        END IF;
    END IF;
END$$

DELIMITER ;
