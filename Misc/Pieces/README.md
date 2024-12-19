# Pieces

## Analysis

The problem provides a text file with a series of strings composed of random characters and numbers. The fact that all strings are of the same size indicates that they are all hashes of some content. More specifically, given that it is 64 characters, it indicates that the hash algorithm is, likely, SHA256.

## Solution

While the strings that generate the hashes in the file could be anything, making breaking them unfeasible, we have to play this from the perspective of the problem. Given that no other files, information, or endpoints are given with the hashes, it stands to reason that one of the hashes, or all of them, would lead directly to the flag. Because in PWNSEC all of the flags follow the same pattern, we know that the flag will start with the pattern `PWNSEC{`. If we hash that pattern using `SHA256` we get the following:

```python
SHA256('PWNSEC{') = 
    f553d31e321f86488de78fb54fd142482ce66758f4758ab8578598740d9e5588
```

We will notice that this hash shows up on the 7th line of our original list. Coincidentally, the string `PWNSEC{` also contains 7 characters. What if we tried removing the `{`?

```python
SHA256('PWNSEC') = 
    4918f93c20b97051fed2fe5472610cda83e5fc5735d2407bd69f322bfe4ed79f
```

That is the 6th line of the original list, which is right before the previous one, and has a single character difference. If we continue removing a single character from this string we will have:

```python
SHA256('P') =
    5c62e091b8c0565f1bafad0dad5934276143ae2ccef7a5381e8ada5b1a8d26d2
SHA256('PW') =
    bc8478052e3ca9de6522b003c9297f7a5319cc5e8477991b48a2402c8c5ced61
SHA256('PWN') =
    6a2a781c47a940d740f7f0e9872c9683088baa559e69a8da81fcad9ad650b990
SHA256('PWNS') =
    481dc61a1bb7b3746b48abc1c76b109cecc88315b684317477e26bd9d023b0fd
SHA256('PWNSE') = 
    7c162e1660c7cbb4297479da406a5104eb5824765aadd97ad3dd58c93553264e
```

These are the first 5 lines of our original file with the hashes. With that, we can conclude that each line adds a single character to the previous line's original string.

If we start at the largest known string, `PWNSEC{`, and brute-force a single character, we will eventually find the string that generates the next hash in the list:

```python
SHA256('PWNSEC{L') = 
    2027b791c448468af66614f8233b0a305a25e7e1486400a9738395c912958575
```

Repeating that process for each of the next hashes, we will find the solution to all remaining hashes:

```python
SHA256('PWNSEC{L3') = 
    330f599093d06de98b4d6e2c91bfb56da7cc2f30cf5005d4b733df4557998f30
SHA256('PWNSEC{L34') = 
    863653956b3fa7d93708fb54441d829468982b29f8486eb830c3cc3744e770cb
SHA256('PWNSEC{L34V') = 
    9400d84df94d54e6acbb9efe8b94ddb51c9cf473a13866dc7b0130adda702f38
# [...]
SHA256('PWNSEC{L34V3_My_C0mpu73R_Y0u_5t4lk3') = 
    a0ce8dfa972be9ea81e6355c0fc72cb05d1ee2bc03915c6942c82b9f7ddadf3a
SHA256('PWNSEC{L34V3_My_C0mpu73R_Y0u_5t4lk3R') = 
    ac87341549192bba1b341cbbc98b94f2e15a32bfdee9b182f7744c8ae7797654
SHA256('PWNSEC{L34V3_My_C0mpu73R_Y0u_5t4lk3R}') = 
    5b0a8dfc3701878ba517924de1264281b7b8f3a4b7ee554db2f247974ca45f2c
```

Our final flag is ```PWNSEC{L34V3_My_C0mpu73R_Y0u_5t4lk3R}```