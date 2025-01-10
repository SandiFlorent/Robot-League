import field_table_tests
import user_table_tests
import championship_table_tests
import team_table_tests
import slot_table_tests

def run_all_tests():
    print("Running all tests...")
    field_table_tests.run_all_tests()
    user_table_tests.run_all_tests()
    championship_table_tests.run_all_tests()
    team_table_tests.run_all_tests()
    slot_table_tests.run_all_tests()

if __name__ == "__main__":
    run_all_tests()
