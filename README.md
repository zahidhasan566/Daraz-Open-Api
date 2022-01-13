Daraz Open API
Developer:MD Zahid Hasan
Email:jahid1234.jh@gmail.com

Used For: Seller API Testing
Testing Process: 1.Using Officical SDK- LazopClient
                 2.Without SDK 
Process 1: Using Officical SDK-LazopClient   
          step 1: Authentication Part (App key, Secret Key)
                Base Url = https://api.daraz.com.bd/rest (BD Link)
          step 2: Request API With parameter or without parameter
                API Name: Your API Name ( For example '/seller/get')
                Request Parameter : Your desired Request with API
          Step 3: Execute The Result with execute Method      
 Process 2: Without SDK    
          step 1: Authentication Part (App key, Secret Key)
                Base Url = https://api.daraz.com.bd/rest (BD Link)
          step 2: Signature Algorithm Apply
                 Signature mathod Working Process:
                 1. Sorting All the parameters( For example: Your App key, Secrect key etc)- Using PHP default Ksort Algorithm
                 2. String Converstation( API Name + Parameter)
                 3. Concat the API and Parametrs
                 4. Make a Sign of 64 bit using Using SHA-256 hash algorithm (PHP default Sha256 Hash algorithm) 
          Step 3: Execute The Result with execute Method 
                 cUrl generate
