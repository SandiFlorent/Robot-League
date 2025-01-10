from sqlalchemy import create_engine, MetaData, Table, insert, exc, update

# The tests deletes the tables after running, so make sure to be running this on a test database
DATABASE_URL = "PasteYourDatabaseURLHere"

# Create the database engine
engine = create_engine(DATABASE_URL)

# Reflect existing tables
metadata = MetaData()
metadata.reflect(bind=engine)
# The team's name
TimeSlot = metadata.tables['slot']

# Testing adding slot with correct dateDebut and dateFin
def test_add_slot_with_correct_dates():
    # Insert a slot
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(TimeSlot).values(dateDebut="2021-01-01", dateFin="2021-02-01"))
                print("Test passed : Slot added with correct dates")
        except exc.IntegrityError:
            print("Test failed")
        finally:
            # Cleanup
            conn.execute(TimeSlot.delete())

# Testing adding slot with dateDebut after dateFin (Expecting failure)
def test_add_slot_with_start_date_after_end_date():
    # Insert a slot
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(TimeSlot).values(dateDebut="2021-02-02", dateFin="2021-02-01"))
                print("Test failed : Slot added with start date after end date")
        except exc.IntegrityError:
            print("Test passed : Slot not added with start date after end date")
        finally:
            # Cleanup
            conn.execute(TimeSlot.delete())
            
def run_all_tests():
    print("Running slot table tests...")
    test_add_slot_with_correct_dates()
    test_add_slot_with_start_date_after_end_date()
            
if __name__ == "__main__":
    test_add_slot_with_correct_dates()
    test_add_slot_with_start_date_after_end_date()
    