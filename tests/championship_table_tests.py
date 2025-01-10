from sqlalchemy import create_engine, MetaData, Table, insert, exc, update

# The tests delete the tables after running, so make sure to be running this on a test database
DATABASE_URL = "PasteYourDatabaseURLHere"

# Create the database engine
engine = create_engine(DATABASE_URL)

# Reflect existing tables
metadata = MetaData()
metadata.reflect(bind=engine)
# The championship table
Championship = metadata.tables['championship']

# Testing creating a championship that behaves as expected
def test_add_championship_with_unique_name():
    # Insert a championship
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(Championship).values(name="Test", description="description", start_date="2021-01-01", end_date="2021-02-01"))
                connection.execute(insert(Championship).values(name="Test2", description="description", start_date="2021-01-01", end_date="2021-02-01"))
                connection.execute(insert(Championship).values(name="Test3", description="description", start_date="2021-01-01", end_date="2021-02-01"))
                print("Test passed : Championship added with unique name")
        except exc.IntegrityError:
            print("Test failed")
        finally:
            # Cleanup
            conn.execute(Championship.delete())
            
# Testing creating a championship with a non-unique name
def test_add_championship_with_non_unique_name():
    # Insert a championship
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(Championship).values(name="Test", description="description", start_date="2021-01-01", end_date="2021-02-01"))
                connection.execute(insert(Championship).values(name="Test", description="description", start_date="2021-01-01", end_date="2021-02-01"))
                connection.execute(insert(Championship).values(name="Test", description="description", start_date="2021-01-01", end_date="2021-02-01"))
                print("Test failed : Championship added with non-unique name")
        except exc.IntegrityError:
            print("Test passed : Championship not added with non-unique name")
        finally:
            # Cleanup
            conn.execute(Championship.delete())
            
# Testing creating a championship with a start date after the end date (Expecting failure)
def test_add_championship_with_start_date_after_end_date():
    # Insert a championship
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(Championship).values(name="Test", description="description", start_date="2021-02-02", end_date="2020-02-01"))
                print("Test failed : Championship added with start date after end date")
        except exc.IntegrityError:
            print("Test passed : Championship not added with start date after end date")
        finally:
            # Cleanup
            conn.execute(Championship.delete())
            
def run_all_tests():
    print("Running championship table tests...")
    test_add_championship_with_unique_name()
    test_add_championship_with_non_unique_name()
    test_add_championship_with_start_date_after_end_date()
            
if __name__ == "__main__":
    test_add_championship_with_unique_name()
    test_add_championship_with_non_unique_name()
    test_add_championship_with_start_date_after_end_date()