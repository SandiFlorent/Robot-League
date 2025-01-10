from sqlalchemy import create_engine, MetaData, Table, insert, exc, update

# The tests deletes the tables after running, so make sure to be running this on a test database
DATABASE_URL = "PasteYourDatabaseURLHere"

# Create the database engine
engine = create_engine(DATABASE_URL)

# Reflect existing tables
metadata = MetaData()
metadata.reflect(bind=engine)
# The team's name
field = metadata.tables['field']

# Testing adding field with unique name
def test_add_field_with_unique_name():
    # Insert a field
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(field).values(name="Test"))
                connection.execute(insert(field).values(name="Test2"))
                connection.execute(insert(field).values(name="Test3"))
                print("Test passed : Field added with unique name")
        except exc.IntegrityError:
            print("Test failed")
        finally:
            # Cleanup
            conn.execute(field.delete())
            
# Testing adding field with non-unique name (Expecting failure)
def test_add_field_with_non_unique_name():
    # Insert a field
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(field).values(name="Test"))
                connection.execute(insert(field).values(name="Test"))
                print("Test failed : Field added with non-unique name")
        except exc.IntegrityError:
            print("Test passed : Field not added with non-unique name")
        finally:
            # Cleanup
            conn.execute(field.delete())
            
def run_all_tests():
    print("Running field table tests...")
    test_add_field_with_unique_name()
    test_add_field_with_non_unique_name()

if __name__ == "__main__":
    test_add_field_with_unique_name()
    test_add_field_with_non_unique_name()