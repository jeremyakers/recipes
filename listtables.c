#include <stdio.h>
#include <sql.h>
#include <sqlext.h>

main() {
   SQLHENV env;
   SQLHDBC dbc;
   SQLHSTMT stmt;
   SQLSMALLINT columns; /* number of columns in result-set */
   SQLCHAR buf[ 5 ][ 64 ];
   int row = 0;
   SQLINTEGER indicator[ 5 ];
   int i;

   /* Allocate an environment handle */
   SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, &env);

   /* We want ODBC 3 support */
   SQLSetEnvAttr(env, SQL_ATTR_ODBC_VERSION, (void *) SQL_OV_ODBC3, 0);

   /* Allocate a connection handle */
   SQLAllocHandle(SQL_HANDLE_DBC, env, &dbc);

   /* Connect to the DSN mydsn */
   /* You will need to change mydsn to one you have created */
   /* and tested */
   SQLDriverConnect(dbc, NULL, "DSN=recipes;", SQL_NTS,
                    NULL, 0, NULL, SQL_DRIVER_COMPLETE);

   /* Allocate a statement handle */
   SQLAllocHandle(SQL_HANDLE_STMT, dbc, &stmt);
   /* Retrieve a list of tables */

//   SQLTables(stmt, NULL, 0, NULL, 0, NULL, 0, "TABLE", SQL_NTS);
   SQLExecDirect(stmt, "SELECT id, name FROM categories ORDER BY id", SQL_NTS);
   /* How many columns are there */

   SQLNumResultCols(stmt, &columns);

   /* Loop through the rows in the result-set binding to */
   /* local variables */
   for (i = 0; i < columns; i++) {
      SQLBindCol( stmt, i + 1, SQL_C_CHAR,
            buf[ i ], sizeof( buf[ i ] ), &indicator[ i ] );
   }

   /* Fetch the data */
   while (SQL_SUCCEEDED(SQLFetch(stmt))) {
      /* display the results that will now be in the bound area's */
      for ( i = 0; i < columns; i ++ ) {
         if (indicator[ i ] == SQL_NULL_DATA) {
            printf("  Column %u : NULL\n", i);
         }
         else {
            printf("  Column %u : %s\n", i, buf[ i ]);
         }
      }
   }
}
