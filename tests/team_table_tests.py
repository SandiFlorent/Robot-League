from sqlalchemy import create_engine, MetaData, Table, insert, exc, update

# The tests deletes the tables after running, so make sure to be running this on a test database
DATABASE_URL = "PasteYourDatabaseURLHere"

# Create the database engine
engine = create_engine(DATABASE_URL)

# Reflect existing tables
metadata = MetaData()
metadata.reflect(bind=engine)
# The team's name
Team = metadata.tables['team']
TeamMember = metadata.tables['team_member']

# Test adding teams with unique names (Expecting success)
def test_add_teams_with_unique_names():
    with engine.connect() as conn:
        try:
            conn.execute(insert(Team).values(name="Team A"))
            conn.execute(insert(Team).values(name="Team B"))
            print("Test passed: Two teams with unique names added successfully.")
        except exc.IntegrityError as e:
            print(f"Test failed: {e}")
        finally:
            # Cleanup
            conn.execute(Team.delete())

# Test adding teams with duplicate names (Expecting failure)
def test_add_teams_with_duplicate_names(): 
    with engine.connect() as conn:
        try:
            # Insert two teams with the same name and score set to 0
            conn.execute(insert(Team).values(name="Team C", score=0))
            conn.execute(insert(Team).values(name="Team C", score=0))
            print("Test failed: Duplicate names were allowed.")
        except exc.IntegrityError:
            print("Test passed: Database prevented duplicate team names.")
        finally:
            # Cleanup
            conn.execute(Team.delete())

# Testing valid goals number (null or positive) (Expecting success)
def test_add_valid_goals():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid goals number
            conn.execute(insert(Team).values(name="Team E", goals=0))
            conn.execute(update(Team).where(Team.c.name == "Team E").values(goals=10))
            conn.execute(update(Team).where(Team.c.name == "Team E").values(goals=4))
            conn.execute(update(Team).where(Team.c.name == "Team E").values(goals=17))
            conn.execute(update(Team).where(Team.c.name == "Team E").values(goals=0))
            print("Test passed: Team with zero or positive goals number added successfully.")
        except exc.IntegrityError as e:
            print(f"Test failed: {e}")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            
# Testing invalid goals number (negative) (Expecting failure)
def test_add_invalid_goals():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid goals number
            conn.execute(insert(Team).values(name="Team E", goals=0))
            conn.execute(update(Team).where(Team.c.name == "Team E").values(goals=-1))
            print("Test Failed: Team with negative goals number added .")
        except exc.IntegrityError as e:
            print("Test passed: Database prevented negative goals number.")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            
# Testing valid wins number (null or positive) (Expecting success)
def test_add_valid_wins():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid wins number
            conn.execute(insert(Team).values(name="Team F", wins=0))
            conn.execute(update(Team).where(Team.c.name == "Team F").values(nb_win=10))
            conn.execute(update(Team).where(Team.c.name == "Team F").values(nb_win=4))
            conn.execute(update(Team).where(Team.c.name == "Team F").values(nb_win=17))
            conn.execute(update(Team).where(Team.c.name == "Team F").values(nb_win=0))
            print("Test passed: Team with zero or positive wins number added successfully.")
        except exc.IntegrityError as e:
            print(f"Test failed: {e}")
        finally:
            # Cleanup
            conn.execute(Team.delete())

# Testing invalid wins number (negative) (Expecting failure)
def test_add_invalid_wins():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid wins number
            conn.execute(insert(Team).values(name="Team F", nb_win=0))
            conn.execute(update(Team).where(Team.c.name == "Team F").values(nb_win=-1))
            print("Test Failed: Team with negative wins number added .")
        except exc.IntegrityError as e:
            print("Test passed: Database prevented negative wins number.")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            
# Testing valid totalPoints (null or positive) (Expecting success)
def test_add_valid_total_points():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid totalPoints
            conn.execute(insert(Team).values(name="Team G", totalPoints=0))
            conn.execute(update(Team).where(Team.c.name == "Team G").values(totalPoints=10))
            conn.execute(update(Team).where(Team.c.name == "Team G").values(totalPoints=4))
            conn.execute(update(Team).where(Team.c.name == "Team G").values(totalPoints=17))
            conn.execute(update(Team).where(Team.c.name == "Team G").values(totalPoints=0))
            print("Test passed: Team with zero or positive totalPoints added successfully.")
        except exc.IntegrityError as e:
            print(f"Test failed: {e}")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            
# Testing invalid totalPoints (negative) (Expecting failure)
def test_add_invalid_total_points():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid totalPoints
            conn.execute(insert(Team).values(name="Team G", totalPoints=0))
            conn.execute(update(Team).where(Team.c.name == "Team G").values(totalPoints=-1))
            print("Test Failed: Team with negative totalPoints added .")
        except exc.IntegrityError as e:
            print("Test passed: Database prevented negative totalPoints.")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            
# Testing valid nb_encounters (null or positive) (Expecting success)
def test_add_valid_nb_encounters():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid nb_encounters
            conn.execute(insert(Team).values(name="Team H", nb_encounters=0))
            conn.execute(update(Team).where(Team.c.name == "Team H").values(nb_encounters=10))
            conn.execute(update(Team).where(Team.c.name == "Team H").values(nb_encounters=4))
            conn.execute(update(Team).where(Team.c.name == "Team H").values(nb_encounters=17))
            conn.execute(update(Team).where(Team.c.name == "Team H").values(nb_encounters=0))
            print("Test passed: Team with null or positive nb_encounters added successfully.")
        except exc.IntegrityError as e:
            print(f"Test failed: {e}")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            
# Testing invalid nb_encounters (negative) (Expecting failure)
def test_add_invalid_nb_encounters():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid nb_encounters
            conn.execute(insert(Team).values(name="Team H", nb_encounters=0))
            conn.execute(update(Team).where(Team.c.name == "Team H").values(nb_encounters=-1))
            print("Test Failed: Team with negative nb_encounters added .")
        except exc.IntegrityError as e:
            print("Test passed: Database prevented negative nb_encounters.")
        finally:
            # Cleanup
            conn.execute(Team.delete())

# Testing valid scores (null or positive) (Expecting success)
def test_add_valid_scores():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid score
            conn.execute(insert(Team).values(name="Team D", score=0))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=10))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=4))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=17))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=0))
            print("Score test : Can add positive score")
            
            # Assert score trigger is working, when we update totalPoints and nb_encounters, score should be updated and equal to totalPoints/nb_encounters
            conn.execute(update(Team).where(Team.c.name == "Team D").values(totalPoints=10))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(nb_encounters=2))
            result = conn.execute(Team.select().where(Team.c.name == "Team D")).fetchone()

            try:
                assert result.score == (result.totalPoints / result.nb_encounters)
                print("Score test : Score is updated correctly by the trigger")
            except AssertionError as e:
                # Handle the exception
                print(f"IntegrityError caught: {e}")
        except exc.IntegrityError as e:
            print(f"Test failed: {e}")
        finally:
            # Cleanup
            conn.execute(Team.delete())

# Testing invalid scores (negative) (Expecting failure)
def test_add_invalid_scores():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid score
            conn.execute(insert(Team).values(name="Team D", score=0))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=-1))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=-3))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=-15))
            conn.execute(update(Team).where(Team.c.name == "Team D").values(score=-9))
            print("Test Failed: Team with negative score added .")
        except exc.IntegrityError as e:
            print("Test passed: Database prevented negative score.")
        finally:
            # Cleanup
            conn.execute(Team.delete())

# Testing that a member's mail in a team can't be there twice in the same team (Expecting success)
def test_add_unique_couple_mail_to_team_member():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid mail
            conn.execute(insert(Team).values(name="Team I"))
            team_id = conn.execute(Team.select().where(Team.c.name == "Team I")).fetchone().id
            conn.execute(insert(TeamMember).values(team_id= team_id, mail="mail1"))
            conn.execute(insert(TeamMember).values(team_id= team_id, mail="mail2"))
            conn.execute(insert(TeamMember).values(team_id= team_id, mail="mail3"))
            conn.execute(insert(TeamMember).values(team_id= team_id, mail="mail4"))
            print("Test passed: Team with different mails added successfully.")
        except exc.IntegrityError as e:
            print(f"Test failed: {e}")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            conn.execute(TeamMember.delete())

def test_add_non_unique_couple_mail_to_team_member():
    with engine.connect() as conn:
        try:
            # Insert a team with a valid mail
            conn.execute(insert(Team).values(name="Team I"))
            team_id = conn.execute(Team.select().where(Team.c.name == "Team I")).fetchone().id
            conn.execute(insert(TeamMember).values(team_id= team_id, mail="mail1"))
            conn.execute(insert(TeamMember).values(team_id= team_id, mail="mail1"))
            print("Test Failed: Team with same mail added .")
        except exc.IntegrityError as e:
            print("Test passed: Database prevented same mail.")
        finally:
            # Cleanup
            conn.execute(Team.delete())
            conn.execute(TeamMember.delete())
            
def run_all_tests():
    print("Running team table tests...")
    test_add_teams_with_unique_names()
    test_add_teams_with_duplicate_names()
    test_add_valid_goals()
    test_add_invalid_goals()
    test_add_valid_wins()
    test_add_invalid_wins()
    test_add_valid_total_points()
    test_add_invalid_total_points()
    test_add_valid_nb_encounters()
    test_add_invalid_nb_encounters()
    test_add_valid_scores()
    test_add_invalid_scores()
    test_add_unique_couple_mail_to_team_member()
    test_add_non_unique_couple_mail_to_team_member()

if __name__ == "__main__":
    test_add_teams_with_unique_names()
    test_add_teams_with_duplicate_names()
    test_add_valid_goals()
    test_add_invalid_goals()
    test_add_valid_wins()
    test_add_invalid_wins()
    test_add_valid_total_points()
    test_add_invalid_total_points()
    test_add_valid_nb_encounters()
    test_add_invalid_nb_encounters()
    test_add_valid_scores()
    test_add_invalid_scores()
    test_add_unique_couple_mail_to_team_member()
    test_add_non_unique_couple_mail_to_team_member()
