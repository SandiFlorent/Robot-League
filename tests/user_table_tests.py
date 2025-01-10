from sqlalchemy import create_engine, MetaData, Table, insert, exc, update

# The tests deletes the tables after running, so make sure to be running this on a test database
DATABASE_URL = "PasteYourDatabaseURLHere"

# Create the database engine
engine = create_engine(DATABASE_URL)

# Reflect existing tables
metadata = MetaData()
metadata.reflect(bind=engine)
# The user table
userTable = metadata.tables['user']

# Testing adding user with unique mail
def test_add_user_with_unique_mail():
    # Insert a user
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(userTable).values(name="Test", mail="mail1@mail.com", password="password"))
                connection.execute(insert(userTable).values(name="Test2", mail="mail2@mail.com", password="password"))
                connection.execute(insert(userTable).values(name="Test3", mail="mail3@mail.com", password="password"))
                print("Test passed : User added with unique mail")
        except exc.IntegrityError:
            print("Test failed")
        finally:
            # Cleanup
            conn.exucute(userTable.delete())


# Testing adding user with non-unique mail
def test_add_user_with_non_unique_mail():
    # Insert a user
    with engine.connect() as conn:
        try:
            with engine.connect() as connection:
                connection.execute(insert(userTable).values(name="Test", mail="mail@mail.com", password="password"))
                connection.execute(insert(userTable).values(name="Test2", mail="mail@mail.com", password="password"))
                print("Test failed")
        except exc.IntegrityError:
            print("Test passed : User not added with non-unique mail")
        finally:
            # Cleanup
            conn.exucute(userTable.delete())
            
def run_all_tests():
    print("Running user table tests...")
    test_add_user_with_unique_mail()
    test_add_user_with_non_unique_mail()

if __name__ == "__main__":
    test_add_user_with_unique_mail()
    test_add_user_with_non_unique_mail()