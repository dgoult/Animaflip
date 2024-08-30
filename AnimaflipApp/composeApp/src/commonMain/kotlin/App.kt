import Model.ConnectedUser
import Model.User
import Service.ApiService
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.material.Button
import androidx.compose.material.MaterialTheme
import androidx.compose.material.Text
import androidx.compose.material.TextField
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import animaflipapp.composeapp.generated.resources.Res
import animaflipapp.composeapp.generated.resources.backward
import chaintech.videoplayer.model.PlayerConfig
import chaintech.videoplayer.ui.video.VideoPlayerView
import org.jetbrains.compose.ui.tooling.preview.Preview
import androidx.compose.runtime.Composable
import androidx.compose.ui.layout.Layout
import kotlinx.coroutines.coroutineScope
import kotlinx.coroutines.launch

@Composable
@Preview
fun App() {
    MaterialTheme {
        var connectedUser: ConnectedUser? by remember { mutableStateOf<ConnectedUser?>(null) }
        var userEmail: String by remember { mutableStateOf("") }
        var userPassword: String by remember { mutableStateOf("") }
        var auth = remember { Auth(apiService = ApiService()) }

        val coroutineScope = rememberCoroutineScope()
//        var themes = remember { ApiService().getSampleThemes() }

        if (connectedUser === null)
        {
            Column(
                modifier = Modifier.fillMaxSize(),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center
            ) {
                TextField(
                    value = userEmail,
                    onValueChange = { userEmail = it },
                    label = { Text("Email") }
                )
                Spacer(modifier = Modifier.height(10.dp))
                TextField(
                    value = userPassword,
                    onValueChange = { userPassword = it },
                    label = { Text("Password") },
                    visualTransformation = PasswordVisualTransformation()
                )
                Spacer(modifier = Modifier.height(20.dp))
                Button(onClick = {
                    coroutineScope.launch {
                        connectedUser = auth.login(userEmail, userPassword)
                    }
                }) {
                    Text("Login")
                }
            }
        } else {
            Column(
                modifier = Modifier.fillMaxSize(),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Top
            ) {
                Text("You are connected")
                Text(connectedUser!!.user.username,
                    style = TextStyle(fontSize = 20.sp),
                    textAlign = TextAlign.Center,
                    modifier = Modifier.fillMaxWidth().align(Alignment.CenterHorizontally))

                val screenSize = remember { mutableStateOf(Pair(-1, -1)) }
                Layout(
                    content = {
                        Box(modifier = Modifier.fillMaxSize()) {
//                            Text("Screen size: ${screenSize.value.first}x${screenSize.value.second}px", modifier = Modifier.align(Alignment.Center))

                            VideoPlayerView(modifier = Modifier.fillMaxWidth().fillMaxHeight(),
                                url = "http://10.0.2.2/videos/cat",
                                playerConfig = PlayerConfig(
                                    isPauseResumeEnabled = true,
                                    isSeekBarVisible = false,
                                    isDurationVisible = false,
                                    isMuteControlEnabled = false,
                                    isSpeedControlEnabled = false,
                                    isFullScreenEnabled = false,
                                    isScreenLockEnabled = false,
                                    seekBarThumbColor = Color.Red,
                                    seekBarActiveTrackColor = Color.Red,
                                    seekBarInactiveTrackColor = Color.White,
                                    seekBarBottomPadding = 10.dp,
                                    pauseResumeIconSize = 100.dp,
                                    isFastForwardBackwardEnabled = true,
                                    fastForwardBackwardIconSize = 300.dp,
                                    isAutoHideControlEnabled = false,
                                    fastBackwardIconResource = Res.drawable.backward
                                )
                            )
                        }
                    },
                    measurePolicy = { measurables, constraints ->
                        // Use the max width and height from the constraints
                        val width = constraints.maxWidth
                        val height = constraints.maxHeight

                        screenSize.value = Pair(width, height)
                        println("Width: $width, height: $height")

                        // Measure and place children composables
                        val placeables = measurables.map { measurable ->
                            measurable.measure(constraints)
                        }

                        layout(width, height) {
                            var yPosition = 0
                            placeables.forEach { placeable ->
                                placeable.placeRelative(x = 0, y = yPosition)
                                yPosition += placeable.height
                            }
                        }
                    }
                )

//                Row(modifier = Modifier.padding(start = 20.dp, top = 10.dp)) {
//                    Box(
//                        modifier = Modifier.fillMaxSize(),
//                        contentAlignment = Alignment.Center
//                    ){
//                        //
//                    }
//                }
//                val navController = rememberNavController()
//                NavHost(navController, startDestination = "themeList") {
//                    composable("themeList") { ThemeListScreen(navController) }
//                    composable("themeDetail/{themeId}") { backStackEntry ->
//                        val themeId = backStackEntry.arguments?.getString("themeId")?.toIntOrNull()
//                        ThemeDetailScreen(themeId)
//                    }
//                }
            }
        }
    }
}

@Composable
fun LoginScreen(viewModel: Auth) {
    var connectedUser: ConnectedUser? by remember { mutableStateOf<ConnectedUser?>(null) }
    var userEmail: String by remember { mutableStateOf("") }
    var userPassword: String by remember { mutableStateOf("") }
    var auth = remember { Auth(apiService = ApiService()) }
    val coroutineScope = rememberCoroutineScope()

    Column(
        modifier = Modifier.fillMaxSize(),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        TextField(
            value = userEmail,
            onValueChange = { userEmail = it },
            label = { Text("Email") }
        )
        Spacer(modifier = Modifier.height(10.dp))
        TextField(
            value = userPassword,
            onValueChange = { userPassword = it },
            label = { Text("Password") },
            visualTransformation = PasswordVisualTransformation()
        )
        Spacer(modifier = Modifier.height(20.dp))
        Button(onClick = {
            coroutineScope.launch {
                connectedUser = auth.login(userEmail, userPassword)
            }
        }) {
            Text("Login")
        }
    }
}